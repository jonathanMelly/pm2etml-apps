<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Jobs') }}
        </h2>
    </x-slot>

    <div class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 gap-4">
            Voici les jobs disponibles:
            @forelse ($jobs as  $job)
                <x-job-card :job="$job" />
            @empty
                <p>{{ __('No jobs') }}</p>
            @endforelse

        </div>
    </div>
</x-app-layout>
