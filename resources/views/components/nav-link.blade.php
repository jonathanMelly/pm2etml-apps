@props(['active','li'])

@php
$classes = 'btn btn-ghost normal-case mx-2' . (($active??false)?' btn-active':'');
@endphp

{!! $li?'<li>':'' !!}
<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
{!! $li?'</li>':'' !!}
