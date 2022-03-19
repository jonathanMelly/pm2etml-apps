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
        $badge = '<div class="badge badge-error">Obligatoire</div>';
    }
    //$badge = '<div class="badge badge-success">'.$job->priority->name.'</div>';

@endphp

<div class="card card-compact w-auto bg-base-100 shadow-xl">
    <figure>
        <img class="object-scale-down h-24" src="/dmz-assets/{{$job->image}}" alt="{{ $job->name }}" />
    </figure>
    <div class="card-body">
        <h2 class="card-title">{{ $job->name }}
            {!! $badge??'' !!}
        </h2>
        <p>{{ $job->description }}</p>
        <div class="card-actions justify-end">
            @foreach(array_map(function($el){ return $el['firstname']; }, $job->clients->toArray()) as $client)
                <button class="btn btn-primary">{{ $client }}</button>
            @endforeach

        </div>
    </div>
</div>

