@props(['li'=>false])

@php
$links = [
'dashboard'=> __('Dashboard'),
'marketplace'=> __('Market place')
];
if (auth()->user()->hasPendingContractApplications()) $links['applications'] = __('Pending applications');
@endphp

@foreach($links as $route=>$label)
<x-nav-link :li="$li" :href="route($route)" :active="request()->routeIs($route)"
    class="btn btn-ghost normal-case mx-4"
    onclick="spin('{{$route}}Button')">
    <span class="hidden" id="{{$route}}Button"></span>{{ $label }}
</x-nav-link>
@endforeach