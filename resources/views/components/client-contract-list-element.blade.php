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

    $hideUponRequest = $wc->alreadyEvaluated()?"x-show=\"!hideAlreadyEvaluated && showGroup_$groupName\"":"x-show=\"showGroup_$groupName\"";
@endphp
<tr {!! $hideUponRequest !!} class="worker-contract">
    <td>
        <label>
            <input type="checkbox" class="checkbox" name="job-{{$job->id}}-contracts[]" value="{{$contract->id}}" data-workers="{{$workers.($wc->name==""?"":" (".$wc->name.")")}}"
            @change="massAction=isAnyChecked('job-{{$job->id}}-contracts[]')">
        </label>
    </td>
    <td>
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
