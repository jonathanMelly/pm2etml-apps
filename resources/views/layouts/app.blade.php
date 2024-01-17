<x-root-layout>
    @push('custom-scripts')
        @once
            <script type="text/javascript" src="{{ URL::asset ('js/helper.js') }}"></script>
        @endonce
    @endpush
    <x-slot name="top">
        @include('layouts.navigation')

        <x-flash-messages />
    </x-slot>

    {{ $slot }}
</x-root-layout>
