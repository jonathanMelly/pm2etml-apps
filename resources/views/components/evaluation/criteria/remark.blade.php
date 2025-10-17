@props(['status_eval', 'studentDetails', 'isTeacher'])

@php
    use App\Constants\AssessmentState;
    use App\Services\AssessmentStateMachine;

    /**
     * On détermine l'état courant
     * Si $status_eval est null, on part sur NOT_EVALUATED
     */
    $currentState = AssessmentState::tryFrom($status_eval) ?? AssessmentState::NOT_EVALUATED;

    /**
     * Une évaluation est considérée "enregistrée"
     * dès qu'on n'est plus dans l'état NOT_EVALUATED
     */
    $hasSavedResult = $currentState !== AssessmentState::NOT_EVALUATED;

    /**
     * On instancie la machine à états pour déterminer l’état suivant
     */
    $machine = new AssessmentStateMachine([
        ['timing' => $currentState->value],
    ]);

    $nextState = $machine->getNextState($isTeacher ? 'teacher' : 'student');

    /**
     * Type d’évaluation pour affichage
     */
    $evaluationType = match ($currentState) {
        AssessmentState::AUTO_FORMATIVE,
        AssessmentState::EVAL_FORMATIVE => __('Formative'),

        AssessmentState::AUTO_FINALE,
        AssessmentState::EVAL_FINALE => __('Finale'),

        default => __('Pas encore évalué'),
    };

    /**
     * Message contextuel selon le rôle
     */
    $placeholderMessage = $isTeacher
        ? __('Ajoutez une remarque globale pour cet élève (visible par lui).')
        : __('Exprimez ici vos impressions générales sur le projet.');
@endphp

{{--  Debug temporaire (à retirer quand tout est validé) --}}
{{-- @dump([
    'student' => "{$studentDetails->student_firstname} {$studentDetails->student_lastname}",
    'status_eval' => $status_eval,
    'currentState' => $currentState->value,
    'nextState' => $nextState?->value ?? null,
    'hasSavedResult' => $hasSavedResult,
    'evaluationType' => $evaluationType,
]) --}}
{{-- /debug --}}

<div class="remark mt-4 mb-4 relative w-full">
    {{-- Titre --}}
    <label for="generalRemark" class="block font-medium text-gray-900 dark:text-gray-200 mb-3">
        {{ __('Remarque générale') }}
    </label>

    <div class="flex flex-col md:flex-row bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        {{-- Zone de texte --}}
        <div class="flex flex-col w-full p-6 space-y-4 border-b md:border-b-0 md:border-r border-gray-300 dark:border-gray-600">
            <div class="relative">
                <textarea id="id-{{ $studentDetails->student_id }}-generalRemark"
                          name="generalRemark"
                          maxlength="10000"
                          placeholder="{{ $placeholderMessage }}"
                          class="textarea textarea-bordered w-full dark:border-gray-600 hover:border-gray-400 
                                 dark:hover:border-gray-500 px-4 py-3 resize-none rounded-md 
                                 focus:outline-none focus:ring-2 focus:ring-blue-500 h-44"></textarea>

                {{-- Compteur caractères --}}
                <span id="charCounter" class="absolute bottom-3 right-3 text-sm text-gray-500 dark:text-gray-400">
                    10000/10000
                </span>
            </div>
        </div>

        {{-- Zone des résultats --}}
        @php
            $wf = $studentDetails->workflow_state ?? null;
            $isClosed = ($wf === \App\Constants\AssessmentWorkflowState::CLOSED_BY_TEACHER->value)
                        || (($studentDetails->status ?? null) === \App\Constants\AssessmentState::COMPLETED->value);
            // Cacher aussi le “live” dès que la sommative enseignant est faite ou au‑delà
            $isSummativeDone = in_array($wf, [
                \App\Constants\AssessmentWorkflowState::TEACHER_SUMMATIVE_DONE->value,
                \App\Constants\AssessmentWorkflowState::CLOSED_BY_TEACHER->value,
            ], true);
        @endphp
        <div class="flex flex-col md:flex-row justify-center items-center p-4 gap-4">
            {{-- Bloc “Résultat enregistré” (uniquement si la BD contient une évaluation) --}}
            @if ($hasSavedResult)
                <div class="flex flex-col items-center animate-fade-in">
                    <x-evaluation.criteria.finalResult
                        :studentId="$studentDetails->student_id"
                        :isTeacher="$isTeacher"
                        :grade="null"
                        :score="null"
                        :evaluationType="$evaluationType"
                        :resultType="'saved'"
                        class="bg-green-100 dark:bg-green-900 rounded-lg p-4 shadow-sm" />
                </div>
            @endif

            {{-- Bloc “Résultat en direct” (masqué si évaluation clôturée) --}}
            @unless($isClosed || $isSummativeDone)
                <div class="flex flex-col items-center">
                    <x-evaluation.criteria.finalResult
                        :studentId="$studentDetails->student_id"
                        :isTeacher="$isTeacher"
                        :grade="null"
                        :score="null"
                        :evaluationType="$evaluationType"
                        :resultType="'live'"
                        class="bg-blue-100 dark:bg-blue-900 rounded-lg p-4 shadow-sm" />
                </div>
            @endunless
        </div>
    </div>
</div>
