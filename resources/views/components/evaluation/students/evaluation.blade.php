@php
    $jsonStudent = collect($jsonSave)->first(fn($student) => $student['student_id'] === $studentDetails->student_id);
    $isUpdate = !empty($jsonStudent['evaluations'] ?? []);

    // Associer chaque catégorie à sa taille et initialiser $order
    $categorySizes = [];
    $order = [];

    foreach ($criteriaGrouped as $category => $criterions) {
        $count = $criterions->count();
        $categorySizes[$category] = $count;
        $order[] = $count;
    }

    // Réorganiser les tailles en groupes de sommes ≤ 4
    $newOrder = [];
    foreach ($order as $value) {
        $placed = false;

        foreach ($newOrder as &$group) {
            if (array_sum($group) + $value <= 4 || array_sum($group) === 4) {
                $group[] = $value;
                $placed = true;
                break;
            }
        }
        if (!$placed) {
            $newOrder[] = [$value];
        }
    }

    // Trier les catégories selon $newOrder
    $sortedCategories = [];
    foreach ($newOrder as $group) {
        foreach ($group as $size) {
            $category = array_search($size, $categorySizes, true);
            if ($category !== false) {
                $sortedCategories[$category] = $criteriaGrouped[$category];
                unset($categorySizes[$category]); // Éviter les doublons
            }
        }
    }

    $status_eval = $jsonStudent['evaluations']['status_eval'] ?? null;

@endphp

<div id="idStudent-{{ $studentDetails->student_id }}-visible"
    data-print-target="student_id-{{ $studentDetails->student_id }}"
    style="{{ !$isFirst ? 'display:none;' : 'display:block;' }}">

    <!-- Conteneur principal en grille -->
    <div class="grid grid-cols-2 gap-4">
        <!-- Colonne gauche : Informations et feedback -->
        <div class="col-span-1">
            <x-evaluation.students.info :isTeacher="$isTeacher" :studentDetails="$studentDetails" data-print="true" />
            <x-evaluation.students.feedback :studentId="$studentDetails->student_id" data-print="true" />
        </div>

        <!-- Colonne droite : Boutons d'action -->
        <div class="col-span-1 text-right">
            <x-evaluation.tabs data-print="false" :status_eval="$status_eval" :studentId="$studentDetails->student_id" :hasEval="$studentDetails->stateMachine" />
        </div>
    </div>

    <!-- Formulaire d'évaluation -->
    <form method="post" action="{{ route('evaluation.storeEvaluation') }}">
        @csrf
        <div class="categories-container text-center space-x-1 space-y-2 w-full"
            id="{{ isset($jsonStudent['evaluations']['id_eval']) ? 'id_eval-' . $jsonStudent['evaluations']['id_eval'] : 'id_evla-undefined' }}">
            @foreach ($sortedCategories as $category => $criterions)
                <x-evaluation.criteria.section :container-id="'id-' . $studentDetails->student_id . '-' . strtolower($category) . '-container'" :category-name="$category" :criterions="$criterions" :visible-sliders="$visibleSliders"
                    :appreciation-labels="$appreciationLabels" :is-teacher="$isTeacher" :evaluation-levels="$evaluationLevels" :id-student="$studentDetails->student_id" :is-visible="$visibleCategories[$category] ?? false"
                    data-print="true" />
            @endforeach

        </div>

        <!-- Remarques sur l'évaluation -->
        <x-evaluation.criteria.remark data-print="true" :status_eval="$status_eval" :studentDetails="$studentDetails" :isTeacher="$isTeacher" />

        <!-- Données cachées pour le formulaire -->
        <input type="hidden" name="evaluation_data" id="evaluation-data-{{ $studentDetails->student_id }}">
        <input type="hidden" name="isUpdate" value="{{ $isUpdate ? 'true' : 'false' }}">
        <input type="hidden" name="ids" value="{{ request()->route('ids') }}">


        <!-- Bouton de soumission -->
        <div class="flex justify-end" data-print="false">
            @if ($status_eval !== 'completed')
                <button type="submit" id="id-{{ $studentDetails->student_id }}-buttonSubmit"
                    class="w-36 p-2 rounded font-semibold text-gray-100 transition 
                {{ $isUpdate ? 'bg-orange-500 hover:bg-orange-600' : 'bg-purple-500 hover:bg-purple-600' }}"
                    data-student-id="{{ $studentDetails->student_id }}"
                    data-update="{{ $isUpdate ? 'true' : 'false' }}">
                    {{ $isUpdate ? __('Update evaluation') : __('Submit evaluation') }}
                </button>
            @else
                {{-- <button type="button" id="id-{{ $studentDetails->student_id }}-buttonPrint"
                    class="w-36 p-2 rounded bg-gray-500 text-gray-100"
                    data-print-id="student_id-{{ $studentDetails->student_id }}" onclick="printSection(this)">
                    {{ __('Print') }}
                </button> --}}
            @endif
        </div>
    </form>
</div>
