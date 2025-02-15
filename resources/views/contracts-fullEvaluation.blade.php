<x-app-layout>
    @push('custom-scripts')
        @once
            @vite(['resources/js/evaluation.js'])
        @endonce
    @endpush

    <div id="eval" class="evaluation-form p-6 relative">
        {{-- Transfert des variables PHP vers JavaScript --}}
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

        {{-- Bouton de personnalisation des critères (visible uniquement pour les enseignants) --}}
        <x-evaluation.criteria.button :route="route('criterias.create')" :label="__('Edit criteria')" :is-teacher="$isTeacher" />

        {{-- Onglets des étudiants --}}
        <x-evaluation.students.tabs :studentsDatas="$studentsDatas" />

        {{-- Conteneur principal pour les évaluations des étudiants --}}
        <div id="ContainerStudentsVisible" class="p-5 mb-4 bg-gray-50 rounded-xl  m-1">
            @foreach ($studentsDatas as $studentDetails)
                <x-evaluation.students.evaluation :studentDetails="$studentDetails" :criteriaGrouped="$criteriaGrouped" :visibleSliders="$visibleSliders"
                    :appreciationLabels="$appreciationLabels" :isTeacher="$isTeacher" :evaluationLevels="$evaluationLevels" :visibleCategories="$visibleCategories" :jsonSave="$jsonSave"
                    :isFirst="$loop->first" />
            @endforeach
        </div>

        {{-- Pagination des étudiants (si la collection est paginée) --}}
        @if ($studentsDatas instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-6">
                {{ $studentsDatas->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
