@php
    $progress = $contract->getProgress();
    $progressPercentage = $progress['percentage'];
    $remainingDays = $progress['remainingDays'];

@endphp
<tr>
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
                    <div class="lg:font-bold lg:text-base text-xs">{{Str::words($contract->jobDefinition->name,3)}}</div>
                </div>
            </div>
        </div>
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
    {{-- TODO create a component for that part so that it is not copy/pasted from client contract list --}}
    <td class="text-left">{{--  overflow-auto doesn’t like right align... --}}
        @if($contract->alreadyEvaluated())
            <div class="tooltip" data-tip="{{$contract->success_date}}">
                @endif
                <i class="ml-7 fa-solid fa-{{$contract->alreadyEvaluated()?($contract->success?'square-check':'square-xmark'):'person-digging'}}"></i>
                @if($contract->alreadyEvaluated())
            </div>
        @endif
    </td>
</tr>
