@props(['flashType','custom'])
@if (($message = Session::get($flashType)) || isset($custom))
<div x-data="{ open: true }" @click="open = ! open" class="sm:mx-6 sm:my-2" x-init="setTimeout(() => open = false, 30000)">
    <div
        x-show="open"
        x-transition:enter="transition ease-in duration-500"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-out duration-1000"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div role="alert" class="alert alert-{{$flashType}} shadow-lg">
            {{$slot}}
            <span>{{$custom??$message??'Oops'}}{!!\Illuminate\Support\Facades\Session::get("printErrors") && $errors->any()?" : <b>".implode(',',$errors->all()).".</b>":""!!}</span>
        </div>

    </div>
</div>
@endif
