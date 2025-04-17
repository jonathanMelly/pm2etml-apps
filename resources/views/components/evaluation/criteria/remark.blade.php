@props(['status_eval', 'studentDetails', 'isTeacher'])

<div class="remark mt-2 mb-2 relative w-full">
    <!-- Titre de la section -->
    <label for="generalRemark" class="w-full block font-medium text-gray-900 dark:text-gray-200 mb-2">
        {{ __('General remark') }}
    </label>

    <!-- Conteneur principal -->
    <div class="flex h-full bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <!-- Première colonne : Zone de texte pour la remarque + ToDo List -->
        <div class="flex flex-col w-full p-4 space-y-4 border-r border-gray-300 dark:border-gray-600">
            <!-- Zone de texte avec compteur de caractères -->
            <div class="relative">
                <textarea id="id-{{ $studentDetails->student_id }}-generalRemark" name="generalRemark" maxlength="10000"
                    {{-- placeholder="{{ __('Add your general remark here...') }}" --}}
                    class="textarea textarea-bordered w-full dark:border-gray-600 hover:border-gray-400 
                    dark:hover:border-gray-500 px-4 py-2 resize-none rounded-md 
                    focus:outline-none focus:ring-2 focus:ring-blue-500 h-40"></textarea>
                <!-- Compteur de caractères (positionné en bas à droite) -->
                <span id="charCounter"
                    class="absolute bottom-2 right-2 text-sm text-gray-500 dark:text-gray-400">10000/10000</span>
            </div>

            {{-- <!-- ToDo List for Teacher -->
            @if ($isTeacher)
                <div id="todo-list-container" class="hidden p-4 mt-4 bg-gray-50 dark:bg-gray-700 rounded-md space-y-4">
                    <!-- Titre de la ToDo List -->
                    <h6 id="msgTodo" class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ __('Please fill all sections') }}
                    </h6>
                    <!-- Liste des tâches -->
                    <div class="todo-item space-y-2 max-h-[10rem] overflow-y-auto ">
                        <!-- Template for to-do item here -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Task Example</span>
                            <button type="button"
                                class="btn btn-danger rounded-full p-1 text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                                X
                            </button>
                        </div>
                    </div>
                    <!-- Bouton Ajouter -->
                    <button type="button"
                        class="btn btn-primary rounded-full px-4 py-2 text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300 w-full"
                        onclick="addTodoItem(this)">
                        {{ __('Add task') }}
                    </button>
                </div>
            @endif --}}
        </div>

        <!-- Deuxième colonne : Résultats (enregistré + en temps réel) -->
        <div class="flex flex-row p-4 gap-4">
            <!-- Affichage du résultat enregistré (si l'étudiant a déjà été évalué) -->
            @if ($studentDetails->stateMachine && $studentDetails->stateMachine->getCurrentState()->value !== 'not_evaluated')
                <x-evaluation.criteria.finalResult :studentId="$studentDetails->student_id" :stateMachine="$studentDetails->stateMachine" :isTeacher="$isTeacher"
                    :grade="'A'" :score="80" :evaluationType="'Formative'" :resultType="'saved'"
                    class="bg-green-100 dark:bg-green-900 rounded-lg p-4 shadow-sm" :remark="__('Evaluated Result')" />
            @endif

            <!-- Affichage du résultat en temps réel (toujours visible) -->
            @if ($status_eval !== 'completed')
                <x-evaluation.criteria.finalResult :studentId="$studentDetails->student_id" :stateMachine="$studentDetails->stateMachine" :isTeacher="$isTeacher"
                    :grade="'A'" :score="100" :evaluationType="'Formative'" :resultType="'live'"
                    class="bg-blue-100 dark:bg-blue-900 rounded-lg p-4 shadow-sm" :remark="__('Live Evaluation')" />
            @endif
        </div>
    </div>
</div>
