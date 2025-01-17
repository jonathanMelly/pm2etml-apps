@props([
    'past'=>false,
    'contract'=>$contract
])
@php
$progress = $contract->getProgress();
$progressPercentage = $progress['percentage'];
$remainingDays = $progress['remainingDays'];

/* @var $contract \App\Models\Contract */
/* @var $wc \App\Models\WorkerContract*/
    $wc = $contract
        ->workersContracts()
        ->whereRelation('groupMember.user','id','=',auth()->user()->id)
        ->first();

    if($wc === null)
    {
        $error = "Could not find worker contract for $contract";
        \Illuminate\Support\Facades\Log::error($error);
        throw new \App\Exceptions\DataIntegrityException($error);
    }

    $canRemediate = $wc->canRemediate();

@endphp
<tr>
    <td>
        <a class="flex items-center space-x-3" href="{{route('jobDefinitions.show',['jobDefinition'=>$contract->jobDefinition->id])}}">
            <div class="avatar">
                <div class="mask mask-squircle w-12 h-12">
                    <img src="{{route('dmz-asset',['file'=>$contract->jobDefinition?->image?->storage_path])}}" alt="{{$contract->jobDefinition->title}}" />
                </div>
            </div>
            <div>
                <div class="indicator">
                    @if(session('contractId')==$contract->id)
                    <span class="indicator-item indicator-start badge badge-primary -mt-2 text-xs">{{__('new')}}</span>
                    @endif
                    @if($wc->remediation_status == \App\Constants\RemediationStatus::ASKED_BY_WORKER)
                        <span class="indicator-item indicator-start badge badge-warning -mt-2 text-xs ml-20">
                            {{__('Remediation request sent')}} <i class="ml-2 fa-solid fa-hourglass"></i>
                        </span>
                    @elseif($wc->remediation_status == \App\Constants\RemediationStatus::REFUSED_BY_CLIENT)
                        <span class="indicator-item indicator-start badge badge-error -mt-2 text-xs ml-6">{{__('Remediation refused')}}</span>
                    @elseif($wc->remediation_status >= \App\Constants\RemediationStatus::CONFIRMED_BY_CLIENT)
                        <span class="indicator-item indicator-start badge badge-info -mt-2 text-xs">{{__('Remediation')}}</span>
                    @endif
                    <div class="lg:font-bold lg:text-base text-xs">{{Str::words($contract->jobDefinition->title,3)}}
                        @if($wc->name!="")
                        ({{$wc->name}})
                        @endif
                    </div>
                </div>
                @if($wc->application_status > 0)
                <div class="indicator-item indicator-start badge badge-warning -mt-2 text-xs" title="Votre engagement n'est pas encore confirmé. Il ne s'agit pour l'instant que d'un souhait de priorité {{ $wc->application_status }}">{{__('Not confirmed')}}</div>
                @endif
            </div>
        </a>
    </td>
    <td>
        {{collect($contract->clients)->transform(fn ($user)=>$user->getFirstnameL())->join(',')}}
        @if((!$wc->alreadyEvaluated() && !$past)|| $canRemediate )
            @if(!$canRemediate)
                <i onclick="switchClient{{$wc->id}}.showModal()" class="fa-solid fa-edit hover:cursor-pointer"></i>
            @endif
        <dialog id="switchClient{{$wc->id}}" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg">
                    @if($canRemediate)
                        {{__('Remediate with')}}</h3>
                <div role="alert" class="alert alert-warning">
                        <i class="fa-solid fa-warning"></i>

                    <span>{{__('Remediation must be first discussed with new client and is subject to validation')}}</span>
                </div>


                    @else
                        {{__('Switch client')}}
                    </h3>
                    @endif


                <p class="py-4">{{__('Select new client')}}</p>
                <form method="post" action="{{route('contracts.update',[$contract])}}" id="contract-{{$contract->id}}-form"
                    x-on:submit.prevent>
                    @method('PATCH')
                    @csrf
                    <x-client-select name="clientId" :selected="$contract->clients->first()->id" :job-definition="$contract->jobDefinition" :with-stats="true" />

                    <label class="input-group flex justify-between mt-2">
                        <div class="self-center justify-self-end">{{__('Start date')}}</div>
                        <input type="date" name="start_date" value="{{old('start_date')??now()->format(\App\DateFormat::HTML_FORMAT)}}"
                               class=" input input-bordered input-primary">
                    </label>

                    <label class="input-group flex justify-between">
                        <div class="self-center justify-self-end">{{__('End date')}}</div>
                        <input type="date" name="end_date" value="{{old('end_date')??now()->addWeeks(3)->format(\App\DateFormat::HTML_FORMAT)}}"
                               class=" input input-secondary input-bordered">
                    </label>
                </form>
                <div class="modal-action">

                    <button class="btn btn-success" onclick="spin('saveButton{{$contract->id}}');document.querySelector('#contract-{{$contract->id}}-form').submit()">
                        <span id="saveButton{{$contract->id}}" class="hidden"></span>
                        {{__('Save')}}
                    </button>

                    <form method="dialog">
                        <!-- if there is a button in form, it will close the modal -->
                        <button class="btn btn-error">{{__('Cancel')}}</button>
                    </form>
                </div>
            </div>
        </dialog>
        @endif
    </td>
    <td>
        {{$contract->start->format(\App\SwissFrenchDateFormat::DATE)}}
    </td>
    <td>
        {{$contract->end->format(\App\SwissFrenchDateFormat::DATE)}}
    </td>
    @if(!$past)
    <td class="text-center">
        <div class="radial-progress" style="--value:{{$progressPercentage}};--size:3rem;--thickness: 2px">{{$progressPercentage}}%</div>
    </td>
    @endif
    {{-- EFFORT --}}
    <td class="text-center">
        {{$wc->getAllocationDetails()}}
    </td>
    @if(!$past)
    <td class="text-center">
        @if($progressPercentage<100)
            {{$remainingDays}} {{$remainingDays>1?__('days'):__('day')}}
            @else
            <i class="fa-solid fa-flag-checkered"></i>
            @endif
    </td>
    @endif
    <x-contract-list-element-evaluation :contract="$contract" :past="$past" />
</tr>
