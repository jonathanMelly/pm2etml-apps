<x-app-layout>

    <div class="sm:mx-6 sm:py-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-1 sm:gap-2 md:gap-3">

        @forelse ($definitions as  $definition)
            <x-job-definition-card :job="$definition" />
        @empty
            <p>{{ __('No jobs') }}</p>
        @endforelse

    </div>

</x-app-layout>
