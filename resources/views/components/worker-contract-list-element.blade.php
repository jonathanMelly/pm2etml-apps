@php
    $progress = $contract->getProgress();
    $progressPercentage = $progress['percentage'];
    $remainingDays = $progress['remainingDays'];

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
                    <div class="lg:font-bold lg:text-base text-xs">{{Str::words($contract->jobDefinition->title,3)}}</div>
                </div>
            </div>
        </a>
    </td>
    <td>
        {{collect($contract->clients)->transform(fn ($user)=>$user->getFirstnameL())->join(',')}}
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
    {{$contract->jobDefinition->getAllocationDetails()}}
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
