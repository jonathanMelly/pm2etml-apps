@props([
'viewOnly' => true,
'job'
])
@php
use \App\Enums\JobPriority;
/*
$badge='<div class="badge badge-xs badge-TYPE">VALUE</div>';
$priorityType=match($job->priority){
JobPriority::MANDATORY=>'success',
JobPriority::RECOMMENDED=>'info',
JobPriority::HIGHLY_RECOMMENDED=>'warning',
JobPriority::BEYOND=>'error'
};
*/

if($job->priority === JobPriority::MANDATORY)
{
//$badgePriority = '<div class="badge badge-sm badge-error">'.__('Mandatory').'</div>';
$mandatoryBadge = '<span class="indicator-item badge badge-secondary bg-opacity-50">'.__('Mandatory').'</span>';
}
//$badge = '<div class="badge badge-success">'.$job->priority->name.'</div>';
//
//$badgePriority = str_replace(['TYPE','VALUE'],[$priorityType,__($job->priority->name)],$badge);
$requiredYears = $job->required_xp_years +1;
$yearStyle = ['info','success','warning','neutral'][$job->required_xp_years];
$priorityStyle = ['error','warning','accent','neutral'][$job->priority->value];


//faster local tests...
if(config('custom.hide-job-image'))
{
$imageSrc='';
$imageBg='bg-base-300';
}
else
{
$mask = \App\Http\Middleware\Theme::timestampToTheme(now()->toDateTime())=='valentine'?'mask mask-heart':'rounded-md';
$imageSrc=route('dmz-asset',['file'=>$job->image?->storage_path]);
}

// To know if unsubscribe must be proposed
$myApplication = $job->pendingApplicationFrom(Auth::user());

@endphp

<div class="flex flex-col">

    <div class="z-10 absolute h-0 flex flex-row">
        <div class="flex flex-row justify-between w-full">
            <div class="flex flex-row">
                @if($job->attachments->isNotEmpty())
                <div class="dropdown dropdown-hover">
                    <label tabindex="0">
                        <img src="{{url('img/paperclip.svg')}}" class="hover:cursor-pointer h-10">
                    </label>
                    <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-96">
                        @foreach($job->attachments as $docAttachment)
                        <li>
                            <a href="{{attachmentUri($docAttachment)}}" download="{{$docAttachment->name}}"><i class="fa-solid fa-paperclip mr-1"></i> {{ Str::limit($docAttachment->name,30,'....'. pathinfo($docAttachment->name)['extension'])}}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                @if($job->skills->isNotEmpty())
                <div class="dropdown dropdown-hover">
                    <label tabindex="0">
                        <img src="{{url('img/badge.svg')}}" class="hover:cursor-help h-10">
                    </label>
                    <ul tabindex="0" class="dropdown-content p-2 shadow bg-base-100 rounded-box w-96 text-xs">
                        <li>{{__('Skills')}}</li>
                        <li class="divider p-0 m-0"></li>
                        @foreach($job->skills as $skill)
                        <li class="my-[0.2em] py-0">
                            <div class="py-0 my-0"><i class="fa-solid fa-check mr-1"></i> {{$skill->getFullName()}}</div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

            <div>
                @if($job->one_shot)
                <div class="tooltip hover:cursor-help" data-tip="{{__('One shot means that as soon as a worker applies for the job, the latter won’t be available to others anymore')}}">
                    <i class="fa-solid fa-meteor fa-xl"></i>
                </div>
                @endif
                @if($job->by_application)
                <div class="tooltip hover:cursor-help" data-tip="{{__('By application means that instead of hiring, we simply take note of the wish to be hired for the job')}}">
                    <i class="fa-solid fa-meteor fa-xl"></i>
                </div>
                @endif
            </div>

        </div>
    </div>

    <div class="h-full mt-2 card card-compact {{$job->one_shot?'border border-opacity-75 border-secondary border-dashed':''}} w-auto bg-base-100 shadow-xl
                {{!$viewOnly?'hover:bg-gradient-to-b hover:from-primary/25 hover:to-base-100 hover:cursor-pointer':''}}"
        onclick="{{!$viewOnly?'window.location.href=\''.route('jobs-apply-for',['jobDefinition'=>$job]).'\'':''}}">
        {{-- DELETE/EVALUATE BUTTONS --}}
        @if($job->providers->contains(Auth::user()) || Auth::user()->can('jobDefinitions'))
        <div class="place-self-end mr-3 mt-2 -mb-6 flex gap-1">
            @can(['jobDefinitions.edit'])
            <div class="rounded-box hover:cursor-pointer hover:bg-info hover:bg-opacity-50">
                <a href="{{route('jobDefinitions.edit',$job)}}">
                    <i class="fa-solid fa-edit"></i>
                </a>
            </div>
            @endcan
            @can('jobDefinitions.trash')
            <div class="rounded-box hover:bg-error hover:bg-opacity-50">
                <a @click="document.querySelector('#delete-job-form').action='{{url('jobDefinitions',$job->id)}}';
                    jobNameToDelete='{{safeJsString($job->title)}}';
                    setTimeout(()=>document.querySelector('#delete-job-modal-submit').disabled=false,3000)">

                    <label for="delete-job-modal" class="hover:cursor-pointer">
                        <i class="fa-solid fa-trash "></i>
                    </label>

                </a>
            </div>
            @endcan
        </div>
        @endif


        <div class="indicator self-center mt-3">
            {!! $mandatoryBadge??'' !!}
            <div class="grid w-24 h-24 place-items-center {{$imageBg??''}}">
                <figure class="mt-1">
                    <img class="object-scale-down {{$mask??''}}" src="{{$imageSrc}}" alt="{{$job->title}}" />
                </figure>
            </div>

        </div>

        @if(!$job->isPublished() || $job->trashed())
        <div class="-skew-y-12 bg-gradient-to-r from-secondary/50 to-secondary text-center">
            {{__($job->trashed()?'Trashed':'Draft')}}
        </div>
        @endif

        <div class="card-body">

            <h2 class="card-title">{{ $job->title }}
                {{--
                <div class="flex flex-col gap-1 items-center">
                {!! $badgePriority??'' !!}
                <div class="badge badge-sm badge-{{$badgeYears}}">{!! $requiredYears.'<sup>'.__(ordinal($requiredYears)).'</sup>&nbsp;'.__('year') !!}
        </div>
    </div>
    --}}
    </h2>
    <p>
        {{ $job->description }}
    </p>

    <div class="grid grid-cols-3 border-secondary border border-opacity-50 rounded divide-y divide-dotted divide-secondary
                    {{$viewOnly?'max-w-fit self-center':''}}">
        <div class="flex justify-end content-center text-sm pr-1">
            {{__('Priority')}}
        </div>
        <div class="col-span-2 justify-start items-center">
            <progress class="progress progress-{{$priorityStyle}} w-20"
                value="{{\App\Enums\JobPriority::last()->value-$job->priority->value}}"
                max="{{\App\Enums\JobPriority::last()->value}}"></progress>
            <span
                class="text-xs">&nbsp;( {{__(Str::ucfirst(Str::lower($job->priority->name)))}} )</span>
        </div>

        <div class="flex justify-end content-center text-sm pr-1">
            {{__('Experience')}}
        </div>
        <div class="col-span-2 justify-start items-center">
            <progress class="progress progress-info w-20" value="{{$job->required_xp_years}}"
                max="3"></progress>
            <span class="text-xs">&nbsp;( {!! $requiredYears.'<sup>'.__(ordinal($requiredYears)).'</sup>&nbsp;'.__('year') !!} )</span>
        </div>

        <div class="flex justify-end content-center text-sm pr-1">
            {{__('Workload')}}
        </div>
        <div class="col-span-2 justify-start items-center">
            <progress class="progress progress-success w-20"
                min="{{\App\Models\JobDefinition::MIN_PERIODS}}"
                value="{{$job->getAllocatedTime(\App\Enums\RequiredTimeUnit::PERIOD)}}"
                max="{{\App\Models\JobDefinition::MAX_PERIODS}}"></progress>
            <span class="text-xs">&nbsp;( {{$job->getAllocationDetails()}} )</span>
        </div>

    </div>
    <div class="card-actions justify-end">
        <i class="text-info-content">{{__('Providers')}}: </i>
        @foreach($job->providers as $provider)
        <a class="btn btn-info btn-outline btn-xs" href="?provider={{$provider->id}}">{{ $provider->getFirstnameL() }}</a>
        @endforeach

    </div>
    @if($myApplication)
    <!-- TODO ask for confirmation -->
    <form method="post" action="{{ route('applications.resign') }}" class="w-auto">
        @csrf
        @method('delete')
        <input type="hidden" id="inpApplicationId" name="applicationid" value="{{ $myApplication->id }}" />
        <button type="submit" class="btn btn-sm btn-warning mt-5">Me désinscrire</button>
    </form>
    @endif
</div>
</div>

</div>
