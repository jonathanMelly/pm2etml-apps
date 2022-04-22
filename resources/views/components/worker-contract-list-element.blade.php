@php
    $totalProgress = $contract->start->diffInDays($contract->end);
    $currentProgress =  $contract->start->diffInDays(now());

    if($currentProgress<0)
    {
        $currentProgress=0;
    }
    else if($currentProgress>$totalProgress)
    {
        $currentProgress=$totalProgress;
    }
    if($totalProgress>0)
    {
        $progressPercentage = round($currentProgress/$totalProgress * 100);
        $remainingDays = $totalProgress-$currentProgress;
    }
    //if contract starts and finishes the same day (TODO: in hours instead of day...)
    else
    {
        $progressPercentage=99;
        $remainingDays=1;
    }

@endphp
<tr class="{{$loop->odd?'active':''}}">
    <td>
        <div class="flex items-center space-x-3">
            <div class="avatar">
                <div class="mask mask-squircle w-12 h-12">
                    <img src="{{img($contract->jobDefinition->image)}}" alt="{{$contract->jobDefinition->name}}" />
                </div>
            </div>
            <div>
                <div class="indicator">
                    @if(session('contractId')==$contract->id)
                    <span class="indicator-item indicator-start badge badge-primary -mt-2 text-xs">{{__('new')}}</span>
                    @endif
                    <div class="grid place-items-center font-bold">{{$contract->jobDefinition->name}}</div>
                </div>
            </div>
        </div>
    </td>
    <td>
        {{$contract->start->format(\App\SwissFrenchDateFormat::FORMAT)}}
    </td>
    <td>
        {{$contract->end->format(\App\SwissFrenchDateFormat::FORMAT)}}
    </td>
    <td class="text-center">
        <div class="radial-progress" style="--value:{{$progressPercentage}};--size:3rem;--thickness: 2px">{{$progressPercentage}}%</div>
    </td>
    <td class="text-center">
    {{$contract->jobDefinition->getAllocationDetails()}}
    </td>
    <td class="text-center">
        @if($progressPercentage<100)
            {{$remainingDays}} {{$remainingDays>1?__('days'):__('day')}}
        @else
            <i class="fa-solid fa-flag-checkered"></i>
        @endif
    </td>
</tr>
