@php
    use \App\Enums\JobPriority;
    /*
    $badge='<div class="badge badge-TYPE">VALUE</div>';
    $type=match($job->priority){
        JobPriority::MANDATORY=>'success',
        JobPriority::RECOMMENDED=>'info',
        JobPriority::HIGHLY_RECOMMENDED=>'warning',
        JobPriority::BEYOND=>'error'
    };
    */
    if($job->priority === JobPriority::MANDATORY)
    {
        $badgePriority = '<div class="badge badge-sm badge-error">'.__('Mandatory').'</div>';
    }
    //$badge = '<div class="badge badge-success">'.$job->priority->name.'</div>';
    //
    $requiredYears = $job->required_xp_years +1;
    $badgeYears = ['info','success','warning','neutral'][$job->required_xp_years];

@endphp

<div class="card card-compact w-auto bg-base-100 shadow-xl">
    <figure class="mt-1">
        <img class="object-scale-down h-24" src="/dmz-assets/{{$job->image}}" alt="{{ $job->name }}" />
    </figure>
    <div class="card-body">
        <h2 class="card-title">{{ $job->name }}
            <div class="flex flex-col gap-1 items-center">
            {!! $badgePriority??'' !!}
            <div class="badge badge-sm badge-{{$badgeYears}}">{!! $requiredYears.'<sup>'.__(ordinal($requiredYears)).'</sup>&nbsp;'.__('year')  !!}</div>
            </div>
        </h2>
        <p>{{ $job->description }}</p>
        <div class="card-actions justify-end">
            <i class="text-primary">{{__('Clients')}}: </i>
            @foreach($job->clients as $client)
                <button class="btn btn-primary btn-outline btn-xs">{{ $client->getFirstnameL() }}</button>
            @endforeach

        </div>
    </div>
</div>

