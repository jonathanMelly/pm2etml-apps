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
        $mandatoryBadge = '<span class="indicator-item badge badge-secondary">'.__('Mandatory').'</span>';
    }
    //$badge = '<div class="badge badge-success">'.$job->priority->name.'</div>';
    //
    //$badgePriority = str_replace(['TYPE','VALUE'],[$priorityType,__($job->priority->name)],$badge);
    $requiredYears = $job->required_xp_years +1;
    $badgeYears = ['info','success','warning','neutral'][$job->required_xp_years];

    if(config('custom.hide-job-image'))
    {
        $image='image';
        $imageBg='bg-base-300';
    }
    else
    {
        $image='<figure class="mt-1">
        <img class="object-scale-down" src="/dmz-assets/'.$job->image.'" alt="'. $job->name.'" />
        </figure>';
    }

@endphp

<div class="card card-compact w-auto bg-base-100 shadow-xl">

    <div class="indicator self-center mt-3">
        {!!  $mandatoryBadge??'' !!}
        <div class="grid w-24 h-24 place-items-center {{$imageBg??''}}">{!! $image !!}</div>
    </div>



    <div class="card-body">

        <h2 class="card-title">{{ $job->name }}
            {{--
            <div class="flex flex-col gap-1 items-center">
            {!! $badgePriority??'' !!}
            <div class="badge badge-sm badge-{{$badgeYears}}">{!! $requiredYears.'<sup>'.__(ordinal($requiredYears)).'</sup>&nbsp;'.__('year')  !!}</div>
            </div>
            --}}
        </h2>
        <p>{{ $job->description }}</p>
        <div class="grid grid-cols-3 border-secondary border border-opacity-50 rounded divide-y divide-dotted divide-secondary">
            <div class="flex justify-end content-center text-sm pr-1">
                {{__('Priority')}}
            </div>
            <div class="col-span-2 justify-start items-center">
                <progress class="progress progress-warning w-20" value="{{\App\Enums\JobPriority::last()->value-$job->priority->value}}" max="{{\App\Enums\JobPriority::last()->value}}"></progress><span class="text-xs">&nbsp;({{__(Str::ucfirst(Str::lower($job->priority->name)))}})</span>
            </div>

            <div class="flex justify-end content-center text-sm pr-1">
                {{__('Experience')}}
            </div>
            <div class="col-span-2 justify-start items-center">
                <progress class="progress progress-info w-20" value="{{$job->required_xp_years}}" max="3"></progress><span class="text-xs">&nbsp;({!! $requiredYears.'<sup>'.__(ordinal($requiredYears)).'</sup>&nbsp;'.__('year')  !!})</span>
            </div>

        </div>
        <div class="card-actions justify-end">
            <i class="text-primary">{{__('Providers')}}: </i>
            @foreach($job->providers as $provider)
                <button class="btn btn-primary btn-outline btn-xs">{{ $provider->getFirstnameL() }}</button>
            @endforeach

        </div>
    </div>
</div>

