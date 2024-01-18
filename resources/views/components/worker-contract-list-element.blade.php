@php
    $progress = $contract->getProgress();
    $progressPercentage = $progress['percentage'];
    $remainingDays = $progress['remainingDays'];

    /* @var $contract \App\Models\Contract */
    /* @var $wc \App\Models\WorkerContract*/
    $wc = $contract->workerContract(auth()->user()->groupMember())->firstOrFail();

@endphp
<tr>
    <td>
        <a class="flex items-center space-x-3" href="{{route('jobDefinitions.show',['jobDefinition'=>$contract->jobDefinition->id])}}">
            <div class="avatar">
                <div class="mask mask-squircle w-12 h-12">
                    <img src="{{route('dmz-asset',['file'=>$contract->jobDefinition->image->storage_path])}}" alt="{{$contract->jobDefinition->title}}" />
                </div>
            </div>
            <div>
                <div class="indicator">
                    @if(session('contractId')==$contract->id)
                    <span class="indicator-item indicator-start badge badge-primary -mt-2 text-xs">{{__('new')}}</span>
                    @endif
                    <div class="lg:font-bold lg:text-base text-xs">{{Str::words($contract->jobDefinition->title,3)}}
                    @if($wc->name!="")
                        ({{$wc->name}})
                    @endif
                    </div>
                </div>
            </div>
        </a>
    </td>
    <td>
        {{collect($contract->clients)->transform(fn ($user)=>$user->getFirstnameL())->join(',')}}
        @if(!$wc->alreadyEvaluated())
            <i onclick="switchClient{{$wc->id}}.showModal()" class="fa-solid fa-edit hover:cursor-pointer"></i>
            <dialog id="switchClient{{$wc->id}}" class="modal">
                <div class="modal-box">
                    <h3 class="font-bold text-lg">{{__('Switch client')}}</h3>
                    <p class="py-4">{{__('Select new client')}}</p>
                    <form method="post" action="{{route('contracts.update',[$contract])}}" id="contract-{{$contract->id}}-form"
                          x-on:submit.prevent>
                        @method('PATCH')
                        @csrf
                        <x-client-select name="clientId" :selected="$contract->clients->first()->id" :job-definition="$contract->jobDefinition" :with-stats="true" />
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
    <td class="text-center">
        <div class="radial-progress" style="--value:{{$progressPercentage}};--size:3rem;--thickness: 2px">{{$progressPercentage}}%</div>
    </td>
    {{-- EFFORT --}}
    <td class="text-center">
    {{$wc->getAllocationDetails()}}
    </td>
    <td class="text-center">
        @if($progressPercentage<100)
            {{$remainingDays}} {{$remainingDays>1?__('days'):__('day')}}
        @else
            <i class="fa-solid fa-flag-checkered"></i>
        @endif
    </td>
    <x-contract-list-element-evaluation :contract="$contract" />
</tr>
