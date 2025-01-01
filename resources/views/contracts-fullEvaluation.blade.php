<x-app-layout>

    <head>
        @vite(['resources/css/app.css', 'resources/js/evaluation.js'])
        <title>Évaluation pratique</title>
    </head>

    <div id="eval" class="evaluation-form space-y-4 p-6 relative">



        <!-- Affichage du nom de l'étudiant avec fond coloré -->
        @foreach ($studentsDatas as $studentDetails)
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


            @if ($isTeacher)
                <!-- Bouton pour modifier les critères personnalisés -->
                <a href="{{ route('create.custom_criterias') }}"
                    class="flex items-center absolute z-10 top-[1.8rem] right-[4.7rem] text-gray-600 hover:text-cyan-400">
                    <svg class="w-6 h-6 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9"></path>
                        <path
                            d="M16.5 3a2.121 2.121 0 0 0-3 0L4.64 11.36a1 1 0 0 0-.29.71v3.59a1 1 0 0 0 1 1h3.59a1 1 0 0 0 .71-.29L21 7.5a2.121 2.121 0 0 0-3-3l-1.5 1.5">
                        </path>
                    </svg>
                    {{ __('fullEvaluation.btnCustomCriteria') }}
                </a>
            @endif

            @php
                // Trouve les données d'évaluation pour l'étudiant actuel
                $jsonStudent = collect($jsonSave)->first(
                    fn($student) => $student['student_Id'] === $studentDetails->student_id,
                );

                // Si des évaluations existent, récupère les niveaux existants
                $isUpdate = isset($jsonStudent) && !empty($jsonStudent['evaluations']);

                $levels = $isUpdate
                    ? collect($jsonStudent['evaluations'])
                        ->flatMap(
                            fn($evaluation) => collect($evaluation['appreciations'])->map(
                                fn($appreciation) => $appreciation['level'] ?? null,
                            ),
                        )
                        ->filter()
                        ->values()
                    : [];

                // Initialiser les variables à false
                $hasEval80 = $hasAuto80 = true;
                $hasEval100 = $hasAuto100 = false;

                // Vérifier les niveaux et mettre à jour les variables correspondantes
                foreach ($levels as $level) {
                    // Comparer l'indice du niveau dans evaluationLevels
                    switch ($level) {
                        case 1: // Équivalent à eval80
                            $hasEval80 = true;
                            break;
                        case 2: // Équivalent à eval100
                            $hasEval100 = true;
                            break;
                        case 3: // Équivalent à auto80
                            $hasAuto80 = true;
                            break;
                        case 4: // Équivalent à auto100
                            $hasAuto100 = true;
                            break;
                    }
                }
            @endphp

            <div id="idStudent-{{ $studentDetails->student_id }}"
                class="student-info mb-6 p-4 bg-gray-200 rounded-lg shadow-sm relative">
                <!-- Affichage des informations selon le rôle de l'utilisateur (enseignant ou étudiant) -->
                @if ($isTeacher)
                    <!-- Informations de l'étudiant (enseignant vue) -->
                    <h3 class="text-xl font-semibold text-blue-600"> {{ __('fullEvaluation.studentLabel') }} :
                        <span
                            class="text-gray-800">{{ $studentDetails->student_firstname .
                                ' ' .
                                $studentDetails->student_lastname .
                                ' (' .
                                $studentDetails->student_id .
                                ')' }}
                        </span>
                    </h3>

                    <h4 class="text-lg font-bold text-gray-700">{{ __('fullEvaluation.projectName') }} : <span
                            class="font-medium">{{ $studentDetails->project_name . ' (' . $studentDetails->job_id . ')' }}</span>
                    </h4>

                    <h4 class="text-lg font-bold text-gray-700">{{ __('fullEvaluation.className') }}<span
                            class="font-medium">{{ $studentDetails->class_name . ' (' . $studentDetails->class_id . ')' }}</span>
                    </h4>

                    <button type="button" class="btn btn-sm bg-blue-500 text-white p-2 rounded absolute top-3 right-2"
                        onclick="toggleVisibility('idStudent-{{ $studentDetails->student_id }}', true)">
                        {{ true ? '▲' : '▼' }}
                    </button>
                @else
                    <!-- Informations de l'enseignant (étudiant vue) -->
                    <h3 class="text-xl font-semibold text-green-700">{{ __('fullEvaluation.teacherName') }} : <span
                            class="text-gray-800">{{ $studentDetails->evaluator_firstname . ' ' . $studentDetails->evaluator_lastname . ' (' . $studentDetails->evaluator_id . ')' }}</span>
                    </h3>
                    <h4 class="text-lg font-bold  text-gray-700">{{ __('fullEvaluation.projectName') }} : <span
                            class="font-medium">{{ $studentDetails->project_name . ' (' . $studentDetails->job_id . ')' }}</span>
                    </h4>

                    <h4 class="text-lg font-bold text-gray-700">{{ __('fullEvaluation.className') }} : <span
                            class="font-medium">{{ $studentDetails->class_name . '(' . $studentDetails->class_id . ')' }}</span>
                    </h4>
                @endif

                <!-- Div pour le petit résultat caché (en haut à droite) -->
                <div id="id-{{ $studentDetails->student_id }}-small_finalResult"
                    class="bg-gray-800 text-white text-sm rounded-xl shadow-lg p-3 absolute top-6 right-[5rem] hidden transition-all duration-500 ease-in-out transform scale-95 hover:scale-100">
                    <h3 class="text-xs font-semibold" id="smallResultTitle"></h3>
                    <p id="smallResultContent" class="text-lg font-medium"> <!-- Contenu caché ici --> </p>
                </div>

                <div id="errors-{{ $studentDetails->student_id }}"
                    class="error-messages text-red-600 font-semibold hidden">
                    <!-- Les erreurs s'afficheront ici dynamiquement -->
                </div>

                <!-- Formulaire de l'évaluation -->
                <form method="post" action="{{ route('evaluation.storeEvaluation') }}" class="space-y-2">
                    @csrf

                    <!-- Onglets d'évaluation -->
                    <div class="evaluation-tabs flex space-x-6" id="id-{{ $studentDetails->student_id }}-btn">
                        @hasanyrole(\App\Constants\RoleName::TEACHER . '|' . \App\Constants\RoleName::STUDENT)
                            @role(\App\Constants\RoleName::TEACHER)
                                <!-- Évaluation 80 -->
                                <button type="button"
                                    class="eval-tab-btn btn {{ $hasEval80 ? 'btn-secondary' : 'btn-outline' }}"
                                    data-level="eval80" onclick="changeTab(this)"
                                    id="id-{{ $studentDetails->student_id }}-btn-eval80">{{ __('fullEvaluation.eval3_4') }}
                                </button>

                                <!-- Évaluation 100 -->
                                <button type="button"
                                    class="eval-tab-btn btn {{ $hasEval100 ? 'btn-secondary' : 'btn-outline' }}"
                                    data-level="eval100" onclick="changeTab(this)"
                                    id="id-{{ $studentDetails->student_id }}-btn-eval100" {{ $hasEval80 ? 'disabled' : '' }}>
                                    {{ __('fullEvaluation.eval100') }}
                                </button>
                            @endrole

                            @role(\App\Constants\RoleName::STUDENT)
                                <!-- Auto-évaluation 80 -->
                                <button type="button"
                                    class="eval-tab-btn btn {{ $hasAuto80 ? 'btn-primary' : 'btn-outline' }}"
                                    data-level="auto80" onclick="changeTab(this)"
                                    id="id-{{ $studentDetails->student_id }}-btn-auto80">{{ __('fullEvaluation.auto3_4') }}
                                </button>
                                <!-- Auto-évaluation 100 -->
                                <button type="button"
                                    class="eval-tab-btn btn {{ $hasAuto100 ? 'btn-primary' : 'btn-outline' }}"
                                    data-level="auto100" onclick="changeTab(this)"
                                    id="id-{{ $studentDetails->student_id }}-btn-auto100"
                                    {{ $hasAuto80 ? 'disabled' : '' }}>{{ __('fullEvaluation.auto100') }}
                                </button>
                            @endrole
                        @endhasanyrole

                        <button type="button" class="eval-tab-btn btn btn-outline btn-success"
                            id="id-{{ $studentDetails->student_id }}-validation-btn"
                            onclick="validateEvaluation('{{ $studentDetails->student_id }}-btn-')">{{ __('fullEvaluation.btnValidation') }}
                        </button>
                    </div>

                    <!-- Section des critères -->
                    <div id="id-{{ $studentDetails->student_id }}-criterias" class="space-y-3">
                        @foreach ($criteriaGrouped as $category => $criterions)
                            <div class="category-container mb-4 p-3 bg-gray-300 rounded-md relative"
                                id="id-{{ $studentDetails->student_id }}-{{ strtolower($category) }}-container"
                                class="category-header flex justify-between items-center mb-4">
                                <h2 class="text-xl font-bold text-cyan-700">
                                    {{ __('fullEvaluation.' . $category) }}
                                </h2>

                                <button type="button"
                                    class="btn btn-sm bg-gray-500 text-white p-2 rounded absolute top-3 right-2"
                                    onclick="toggleVisibility('id-{{ $studentDetails->student_id }}-{{ strtolower($category) }}-container')">
                                    {{ $visibleCategories[$category] ? '▲' : '▼' }}
                                </button>

                                <!-- Grille de critères (4 colonnes) avec fond pour chaque critère -->
                                <div class="criteria-grid grid gap-2 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
                                    style="display: $visibleCategories[$category] ? 'block' : 'none' }}">
                                    {{-- attention doit implémenter l'id de l'user pour etre unique --}}
                                    @foreach ($criterions as $criterion)
                                        <div class="mt-2 relative group">
                                            <span
                                                class="hidden absolute bg-gray-700 text-white p-2 rounded mt-[-50px] shadow-lg"
                                                id="description-{{ $criterion['id'] }}">
                                                {{ __('fullEvaluation.criterias' . $criterion['id'] . '_description') }}
                                            </span>
                                            <x-criterion-card :criterion="$criterion" :visible-sliders="$visibleSliders" :appreciation-labels="$appreciationLabels"
                                                :is-teacher="$isTeacher" :evaluation-levels="$evaluationLevels" :id-student="$studentDetails->student_id" />
                                        </div>
                                    @endforeach

                                    <!-- Script pour afficher la popup d'aide concernant le critère à évaluer "handover" -->

                                    <script>
                                        document.querySelectorAll('.group').forEach(function(element) {
                                            let timeout;

                                            element.addEventListener('mouseenter', function() {
                                                const description = element.querySelector('span');
                                                timeout = setTimeout(function() {
                                                    description.classList.remove('hidden');
                                                    description.classList.add('block');
                                                }, 7000); // 7 secondes
                                            });

                                            element.addEventListener('mouseleave', function() {
                                                const description = element.querySelector('span');
                                                clearTimeout(timeout); // Annule le setTimeout
                                                description.classList.remove('block');
                                                description.classList.add('hidden');
                                            });
                                        });
                                    </script>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Remarque générale -->
                    <div class="general-remark mt-4 flex flex-col lg:flex-row gap-6 items-end">
                        <!-- Colonne 85% -->
                        <div class="w-full lg:w-[90%]">
                            <label for="id-{{ $studentDetails->student_id }}-generalRemark"
                                class="text-xl font-semibold text-cyan-600 block mb-2">
                                {{ __('fullEvaluation.general_remark') }}
                            </label>

                            <textarea id="id-{{ $studentDetails->student_id }}-generalRemark" name="generalRemark"
                                class="textarea textarea-bordered bg-gray-100 w-full h-48 resize-none rounded-md shadow-sm p-2 border-gray-300 focus:ring-cyan-500 focus:border-cyan-500">
                            </textarea>
                        </div>

                        <!-- Colonne 15%, alignée en bas -->
                        <div id="id-{{ $studentDetails->student_id }}-finalResult"
                            class="lg:w-[10%] w-full flex-col items-center lg:items-start relative ">
                            <div class="w-full bg-orange-100 rounded-md h-48 shadow-sm p-6 flex flex-col items-center">
                                <h3 id="finalResultTitle" class="font-semibold text-xl text-gray-800 ">
                                    <!-- Titre dynamique selon l'évaluation -->
                                    {{ $isTeacher ? __('fullEvaluation.msgFormative') : __('fullEvaluation.msgAutoEval') }}
                                </h3>

                                <p id="finalResultContent" class="text-lg font-medium text-gray-500 mt-10">
                                    <!-- Résultat ici par. Résultat par défaut NA-->
                                    A
                                </p>
                                <span class="text-xs absolute bottom-1 right-1" id='spanResult'> 80% </span>
                            </div>
                        </div>
                    </div>


                    <input type="hidden" name="evaluation_data"
                        id="evaluation-data-{{ $studentDetails->student_id }}">
                    <div class="flex justify-end">
                        <!-- Champ caché pour indiquer s'il s'agit d'une mise à jour -->
                        <input type="hidden" name="isUpdate" value="{{ $isUpdate ? 'true' : 'false' }}">

                        <!-- Affichage dynamique des boutons -->
                        <button type="submit" id="id-{{ $studentDetails->student_id }}-buttonSubmit"
                            class="p-2 rounded {{ $isUpdate ? 'bg-orange-500 hover:bg-orange-600' : 'bg-purple-500 hover:bg-purple-600' }} font-semibold text-gray-100"
                            data-student-id="{{ $studentDetails->student_id }}"
                            data-update="{{ $isUpdate ? true : false }}">
                            {{ $isUpdate ? __('fullEvaluation.msgUpdateEval') : __('fullEvaluation.msgSubmitEval') }}
                        </button>
                    </div>


                    <!-- Affichage des niveaux pour vérification -->
                    {{-- @if ($isUpdate)
                        <div>
                            <strong>Niveaux d'évaluation actuels :</strong>
                            <ul>
                                @foreach ($levels as $level)
                                    <li>Évaluation Niveau: {{ $level }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif --}}

                </form>
            </div>
        @endforeach
    </div>
</x-app-layout>
