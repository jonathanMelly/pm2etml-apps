<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Jobs') }}
        </h2>
    </x-slot>

    <div class="py-6 ">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-1 sm:gap-2 md:gap-3">

            @forelse ($jobs as  $job)
                <x-job-card :job="$job" />
            @empty
                <p>{{ __('No jobs') }}</p>
            @endforelse

        </div>
    </div>
</x-app-layout>
