<x-root-layout>
    @push('custom-scripts')
        @once
            @vite(['resources/js/helper.js'])
        @endonce
    @endpush
    <x-slot name="top">
        @include('layouts.navigation')

        <x-flash-messages />
    </x-slot>

    {{ $slot }}
</x-root-layout>
