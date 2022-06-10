<x-app-layout>

    <div class="sm:mx-6 bg-base-200 rounded-box sm:p-3 p-1" x-data="{jobNameToDelete:''}">

        <div class="drawer">
            <input id="my-drawer" type="checkbox" class="drawer-toggle" />
            <div class="drawer-content">
                <!-- Page content here -->
                <label for="my-drawer" class="btn btn-primary drawer-button">Open drawer</label>
            </div>
            <div class="drawer-side">
                <label for="my-drawer" class="drawer-overlay"></label>
                <ul class="menu p-4 overflow-y-auto w-80 bg-base-100 text-base-content">
                    <!-- Sidebar content here -->
                    <li><a>Sidebar Item 1</a></li>
                    <li><a>Sidebar Item 2</a></li>

                </ul>
            </div>
        </div>

        @can('jobDefinitions.create')
        <div class="pb-2">
            <a class="btn btn-sm" href="{{route('jobDefinitions.create')}}"><i class="fa-solid fa-plus fa-lg"></i></a>
        </div>
        @endcan

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-1 sm:gap-2 md:gap-3">
            @forelse ($definitions as  $definition)
                <x-job-definition-card :job="$definition" :view-only="Auth::user()->cannot('jobs-apply') || Auth::user()->isAdmin()"/>
            @empty
                <p>{{ __('No jobs') }}</p>
            @endforelse
        </div>

        <x-job-definition-card-delete-modal />

    </div>

</x-app-layout>
