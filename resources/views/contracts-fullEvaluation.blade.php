<x-app-layout>
    @push('custom-scripts')
        @once
            @vite(['resources/js/evaluation.js'])
        @endonce
    @endpush

    <div id="eval" class="evaluation-form space-y-4 p-6 relative">

        <button type="button"
            class="btn btn-sm bg-teal-400 text-white px-6 py-2 rounded absolute right-6 top-2 border-collapse border-cyan-400"
            onclick="toggleVisibility('all','true')">▲▲▲
        </button>

        <!-- Injection du script d'état -->
        <x-script-state :state="[
            'visibleCategories' => $visibleCategories,
            'visibleSliders' => $visibleSliders,
            'evaluationLevels' => $evaluationLevels,
            'appreciationLabels' => $appreciationLabels,
            'criteriaGrouped' => $criteriaGrouped,
            'isTeacher' => $isTeacher,
            'studentData' => $studentsDatas,
            'jsonSave' => $jsonSave,
        ]" />

        <x-custom-criteria-button :route="route('criterias.create')" :label="__('Edit criteria')" :is-teacher="$isTeacher" />

        <!-- Affichage du nom de l'étudiant avec fond coloré -->
        @foreach ($studentsDatas as $studentDetails)
            @php
                // Recherche les données d'évaluation pour l'étudiant actuel
                $jsonStudent = collect($jsonSave)->first(
                    fn($student) => $student['student_Id'] === $studentDetails->student_id,
                );

                $isUpdate = isset($jsonStudent['evaluations']) && !empty($jsonStudent['evaluations']);
                // Vérifie si des évaluations existent pour l'étudiant actuel
            @endphp


            <div id="idStudent-{{ $studentDetails->student_id }}"
                class="student-info mb-6 p-4 bg-gray-200 rounded-lg shadow-sm relative">

                <x-student-info :isTeacher="$isTeacher" :studentDetails="$studentDetails" />

                <!-- Div pour le petit résultat caché (en haut à droite) -->
                <div id="id-{{ $studentDetails->student_id }}-small_finalResult"
                    class=" text-white text-sm rounded-xl shadow-lg p-3 absolute top-10 right-24
                    hidden transition-all duration-500 ease-in-out transform scale-95 hover:scale-100">
                    <h3 class="text-xs font-semibold" id="smallResultTitle-{{ $studentDetails->student_id }}"></h3>
                    <p id="smallResultContent" class="text-lg font-medium"> <!-- Contenu caché ici --> </p>
                </div>

                <div id="errors-{{ $studentDetails->student_id }}"
                    class="error-messages text-red-600 font-semibold hidden">
                    <!-- Les erreurs s'afficheront ici dynamiquement -->
                </div>

                <!-- Formulaire de l'évaluation -->
                <form method="post" action="{{ route('evaluation.storeEvaluation') }}" class="space-y-2">
                    @csrf

                    <x-evaluation-tabs :studentId="$studentDetails->student_id" :hasEval="$studentDetails->stateMachine" />

                    <div id="id-{{ $studentDetails->student_id }}-criterias" class="space-y-3">
                        @foreach ($criteriaGrouped as $category => $criterions)
                            <x-criterion-section :container-id="'id-' . $studentDetails->student_id . '-' . strtolower($category) . '-container'" :category-name="$category" :criterions="$criterions"
                                :visible-sliders="$visibleSliders" :appreciation-labels="$appreciationLabels" :is-teacher="$isTeacher" :evaluation-levels="$evaluationLevels"
                                :id-student="$studentDetails->student_id" :is-visible="$visibleCategories[$category]" />
                        @endforeach
                    </div>

                    {{-- Champ remarque --}}
                    <x-general-remark :studentDetails="$studentDetails" :isTeacher="$isTeacher" />

                    <input type="hidden" name="evaluation_data" id="evaluation-data-{{ $studentDetails->student_id }}">

                    <div class="flex justify-end">
                        <!-- Champ caché pour indiquer s'il s'agit d'une mise à jour -->
                        <input type="hidden" name="isUpdate" value="{{ $isUpdate ? 'true' : 'false' }}">

                        <!-- Affichage dynamique des boutons -->
                        <button type="submit" id="id-{{ $studentDetails->student_id }}-buttonSubmit"
                            class="p-2 rounded {{ $isUpdate ? 'bg-orange-500 hover:bg-orange-600' : 'bg-purple-500 hover:bg-purple-600' }} font-semibold text-gray-100"
                            data-student-id="{{ $studentDetails->student_id }}"
                            data-update="{{ $isUpdate ? true : false }}">
                            {{ $isUpdate ? __('Update evaluation') : __('Submit evaluation') }}
                        </button>
                    </div>
                </form>
            </div>
        @endforeach
        <!-- Liens de pagination -->
        @if ($studentsDatas instanceof \Illuminate\Pagination\LengthAwarePaginator)
            {{ $studentsDatas->links() }}
        @endif
    </div>

</x-app-layout>
