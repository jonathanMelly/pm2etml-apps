@php
    $progress = $contract->getProgress();
    $progressPercentage = $progress['percentage'];
    $remainingDays = $progress['remainingDays'];

    $workers=collect($contract->workers)->transform(fn ($gm)=>$gm->user->getFirstnameL())->join(',');

    /* @var $contract \App\Models\Contract */
    /* @var $wc \App\Models\WorkerContract */
    $wc = $contract->workerContract($contract->workers[0])->firstOrFail();
    $groupName = $contract->workers[0]->group->groupName->name;

    $hideDone = false;

    $hideUponRequest = $wc->alreadyEvaluated() ?
        "x-show=\"!hideAlreadyEvaluated && showGroup_$groupName\""
        :"x-show=\"showGroup_$groupName\"";
@endphp
<tr {!! $hideUponRequest !!} class="worker-contract">
    <td>
        <label>
            <input type="checkbox" class="checkbox" name="job-{{$job->id}}-contracts[]" value="{{$contract->id}}" data-workers="{{$workers.($wc->name==""?"":" (".$wc->name.")")}}"
            @change="massAction=isAnyChecked('job-{{$job->id}}-contracts[]')">
        </label>
    </td>
    <td>
        @if($wc->remediation_status == \App\Constants\RemediationStatus::ASKED_BY_WORKER)

            <dialog id="acceptRemediation{{$wc->id}}" class="modal">
                <div class="modal-box">
                    <h3 class="font-bold text-lg">{{__('Validate remediation request for :name and project :project',["name"=>$workers,"project"=>$contract->jobDefinition->title])}} ?</h3>

                    <div class="modal-action">
                        <form>{{-- Firts form seems to be eaten by something ??? --}}</form>
                        <form method="post" action="{{route('contracts.update',[$contract])}}" id="remediation-{{$contract->id}}-form" x-on:submit.prevent>
                            @method('PATCH')
                            @csrf
                            <input type="hidden" name="remediation-accept" value="1" id="rem-{{$contract->id}}">
                            <input type="submit" class="hidden" value="{{__('Yes')}}">{{-- for laravel simple test frmwk --}}
                            <button class="btn btn-success" onclick="spin('remYes{{$contract->id}}');
                                document.getElementById('remediation-{{$contract->id}}-form').submit()">
                                <span id="remYes{{$contract->id}}" class="hidden"></span>
                                {{__('Yes')}}
                            </button>
                            <button class="btn btn-error ml-1" onclick="document.getElementById('rem-{{$contract->id}}').value='0';
                                spin('remNo{{$contract->id}}');document.getElementById('remediation-{{$contract->id}}-form').submit()">
                                <span id="remNo{{$contract->id}}" class="hidden"></span>
                                {{__('No')}}
                            </button>

                        </form>


                        <form method="dialog">
                            <!-- if there is a button in form, it will close the modal -->
                            <button class="btn btn-outline ml-1">{{__('Cancel')}}</button>
                        </form>
                    </div>
                </div>
            </dialog>


            <span class="indicator-item indicator-start badge badge-warning -mt-2 text-xs cursor-pointer"
                  onclick="acceptRemediation{{$wc->id}}.showModal()">
                {{__('Remediation request')}}
                <i class="ml-2 fa-solid fa-arrow-right" ></i>
            </span>
        @elseif($wc->remediation_status === \App\Constants\RemediationStatus::REFUSED_BY_CLIENT)
            <span class="indicator-item indicator-start badge badge-error -mt-2 text-xs">{{__('Remediation refused')}}</span>
        @elseif($wc->remediation_status >= \App\Constants\RemediationStatus::CONFIRMED_BY_CLIENT)
            <span class="indicator-item indicator-start badge badge-info -mt-2 text-xs">{{__('Remediation')}}</span>
        @endif
        {{$wc->name==""?__("main"):$wc->name}} ({{$wc->getAllocatedTime()}}p)
    </td>
    <td>
        {{$groupName}}
    </td>
    <td>
        {{$workers}}
    </td>
    <td>
        {{$contract->start->format(\App\SwissFrenchDateFormat::DATE)}}
    </td>
    <td>
        {{$contract->end->format(\App\SwissFrenchDateFormat::DATE)}}
    </td>
    <td class="w-36">
        <progress class="progress progress-accent w-36" value="{{$progressPercentage}}" max="100"></progress> ({{$progressPercentage}}%)
    </td>

    <td class="text-center">
        @if($progressPercentage<100)
            {{$remainingDays}} {{$remainingDays>1?__('days'):__('day')}}
        @else
            <i class="fa-solid fa-flag-checkered"></i>
        @endif
    </td>

    <x-contract-list-element-evaluation :job="$job"  :contract="$contract" />
</tr>
