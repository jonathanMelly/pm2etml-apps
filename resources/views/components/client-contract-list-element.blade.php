@php
    $progress = $contract->getProgress();
    $progressPercentage = $progress['percentage'];
    $remainingDays = $progress['remainingDays'];
@endphp
<tr>
    <td>
        <label>
            <input type="checkbox" class="checkbox" name="contracts[]" value="{{$contract->id}}">
        </label>
    </td>
    <td>
        {{$contract->workers[0]->group->groupName->name}}
    </td>
    <td>
        {{collect($contract->workers)->transform(fn ($gm)=>$gm->user->getFirstnameL())->join(',')}}
    </td>
    <td>
        {{$contract->start->format(\App\SwissFrenchDateFormat::FORMAT)}}
    </td>
    <td>
        {{$contract->end->format(\App\SwissFrenchDateFormat::FORMAT)}}
    </td>
    <td class="w-36">
        <progress class="progress progress-success w-36" value="{{$progressPercentage}}" max="100"></progress> ({{$progressPercentage}}%)
    </td>

    <td class="text-right">
        @if($progressPercentage<100)
            {{$remainingDays}} {{$remainingDays>1?__('days'):__('day')}}
        @else
            <i class="fa-solid fa-flag-checkered"></i>
        @endif
    </td>
</tr>
