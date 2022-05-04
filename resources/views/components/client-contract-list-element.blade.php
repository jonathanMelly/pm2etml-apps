@php
    $progress = $contract->getProgress();
    $progressPercentage = $progress['percentage'];
    $remainingDays = $progress['remainingDays'];

    $workers=collect($contract->workers)->transform(fn ($gm)=>$gm->user->getFirstnameL())->join(',')
@endphp
<tr>
    <td>
        <label>
            <input type="checkbox" class="checkbox" name="job-{{$job->id}}-contracts[]" value="{{$contract->id}}" data-workers="{{$workers}}"
            @change="massAction=isAnyChecked('job-{{$job->id}}-contracts[]')">
        </label>
    </td>
    <td>
        {{$contract->workers[0]->group->groupName->name}}
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
        <progress class="progress progress-success w-36" value="{{$progressPercentage}}" max="100"></progress> ({{$progressPercentage}}%)
    </td>

    <td class="text-center">
        @if($progressPercentage<100)
            {{$remainingDays}} {{$remainingDays>1?__('days'):__('day')}}
        @else
            <i class="fa-solid fa-flag-checkered"></i>
        @endif
    </td>

    <td class="text-right">
        @if($contract->alreadyEvaluated())
            <div class="tooltip" data-tip="{{$contract->success_date}}">
        @endif
        <i class="fa-solid fa-{{$contract->alreadyEvaluated()?($contract->success?'square-check':'square-xmark'):'person-digging'}}"></i>
        @if($contract->alreadyEvaluated())
            </div>
        @endif
    </td>
</tr>
