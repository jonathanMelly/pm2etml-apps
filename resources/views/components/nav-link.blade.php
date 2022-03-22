@props(['active','li'])

@php
$classes = (($active??false)?' btn-active':'');
@endphp

{!! $li?'<li>':'' !!}
<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
{!! $li?'</li>':'' !!}
