<x-app-layout>

    <div class="sm:mx-6 bg-base-200 rounded-box sm:p-3 p-1" x-data="{jobNameToDelete:''}">

        @can('jobDefinitions.create')
        <div class="pb-2">
            <a class="btn btn-sm" href="{{route('jobDefinitions.create')}}"><i class="fa-solid fa-plus fa-lg"></i></a>
        </div>
        @endcan

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-1 sm:gap-2 md:gap-3">
            @forelse ($definitions as  $definition)
                <x-job-definition-card :job="$definition" :view-only="Auth::user()->cannot('jobs-apply')"/>
            @empty
                <p>{{ __('No jobs') }}</p>
            @endforelse
        </div>

        <input type="checkbox" id="delete-job-modal" class="modal-toggle">
        <div class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg">{{__('Do you really want to delete the following job ?',)}}</h3>
                <p class="py-4">

                <div class="flex flex-wrap">
                    <div class="w-1/4">
                        <i class="fa-solid fa-project-diagram fa-align-center mr-2"></i> <strong> {{__('Project')}}
                            :</strong>
                    </div>
                    <div class="w-3/4" x-text="jobNameToDelete">

                    </div>
                </div>
                </p>

                <form id="delete-job-form" method="post">
                    @csrf
                    @method('delete')

                </form>

                <div class="modal-action">
                    <button id="delete-job-modal-submit" disabled
                            @click="document.querySelector('#delete-job-form').submit()"
                            type="button" class="btn btn-outline btn-error">{{__('Yes')}}</button>

                    <label for="delete-job-modal" class="btn"
                           @click="document.querySelector('#delete-job-modal-submit').disabled=true">{{__('No')}}</label>
                </div>

            </div>
        </div>
    </div>

</x-app-layout>
