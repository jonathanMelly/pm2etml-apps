<div>
<li>{{ $job->name }} [ {{ implode(', ', array_map(function($el){ return $el['email']; }, $job->clients->toArray())) }}]
    <img height="50px" src="/dmz-assets/{{$job->image}}">
</li>
</div>
