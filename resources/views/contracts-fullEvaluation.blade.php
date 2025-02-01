<x-app-layout>
    @push('custom-scripts')
        @once
            @vite(['resources/js/evaluation.js'])
        @endonce
    @endpush

    <div id="eval" class="evaluation-form p-6 relative">
        <x-evaluation.state :state="[
            'visibleCategories' => $visibleCategories,
            'visibleSliders' => $visibleSliders,
            'evaluationLevels' => $evaluationLevels,
            'appreciationLabels' => $appreciationLabels,
            'criteriaGrouped' => $criteriaGrouped,
            'isTeacher' => $isTeacher,
            'studentData' => $studentsDatas,
            'jsonSave' => $jsonSave,
        ]" />

        <x-evaluation.criteria.button :route="route('criterias.create')" :label="__('Edit criteria')" :is-teacher="$isTeacher" />

        <x-evaluation.students.tabs :studentsDatas="$studentsDatas" />

        <div id="ContainerStudentsVisible" class="p-4 mb-6 bg-gray-100 relative">
            @foreach ($studentsDatas as $studentDetails)
                <x-evaluation.students.evaluation :studentDetails="$studentDetails" :criteriaGrouped="$criteriaGrouped" :visibleSliders="$visibleSliders"
                    :appreciationLabels="$appreciationLabels" :isTeacher="$isTeacher" :evaluationLevels="$evaluationLevels" :visibleCategories="$visibleCategories" :jsonSave="$jsonSave"
                    :isFirst="$loop->first" />
            @endforeach
        </div>

        @if ($studentsDatas instanceof \Illuminate\Pagination\LengthAwarePaginator)
            {{ $studentsDatas->links() }}
        @endif
    </div>
</x-app-layout>
