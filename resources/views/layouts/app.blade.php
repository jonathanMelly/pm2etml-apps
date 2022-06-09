<x-root-layout>
    <x-slot name="top">
        @include('layouts.navigation')

        <x-flash-messages />
    </x-slot>

    {{ $slot }}
</x-root-layout>
