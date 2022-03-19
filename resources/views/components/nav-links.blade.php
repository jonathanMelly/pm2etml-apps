@props(['li'=>false])

@php
    $links = [
        'dashboard'=> __('Dashboard'),
        'jobs'=> __('Jobs')
    ];
@endphp

@foreach($links as $route=>$label)
    <x-nav-link :li="$li" :href="route($route)" :active="request()->routeIs($route)">
        {{ $label }}
    </x-nav-link>
@endforeach


