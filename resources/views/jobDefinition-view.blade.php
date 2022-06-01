<x-app-layout>

    <div class="sm:mx-6 py-6 rounded-box bg-base-200 flex flex-col gap-2 items-center" x-data="{jobNameToDelete:''}">

        <x-job-definition-card :job="$jobDefinition" :view-only="true" />

        <x-job-definition-card-delete-modal />

    </div>
</x-app-layout>
