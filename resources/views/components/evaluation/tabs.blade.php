<div class="evaluation-tabs flex space-x-6 relative justify-end" id="id-{{ $studentId }}-btn"
    data-current-state="{{ optional(optional($hasEval)->getCurrentState())->value ?? 'not_evaluated' }}"
    data-next-state-teacher="{{ $hasEval ? $hasEval->getNextState('teacher')->value : null }}"
    data-next-state-student="{{ $hasEval ? $hasEval->getNextState('student')->value : null }}">

    @php
        if (!function_exists('isCurrentState')) {
            function isCurrentState($desiredState, $currentState)
            {
                return $desiredState === $currentState;
            }
        }

        // État actuel avec fallback
        $currentState = optional(optional($hasEval)->getCurrentState())->value ?? 'not_evaluated';

        // Variables pour simplifier les conditions
        $isEvaluated = in_array($currentState, ['eval80', 'eval100']);
        $isAutoEvaluated = in_array($currentState, ['auto80', 'auto100']);
        $canValidate = !in_array($currentState, ['pending_signature', 'completed']);

        $nextStateMessages = [
            'eval80' => __('Validate formative eval to proceed.'),
            'auto80' => __('Student awaits your validation.'),
            'auto100' => __('Awaiting teacher eval.'),
            'eval100' => __('Awaiting student validation.'),
        ];
    @endphp

    @hasanyrole(\App\Constants\RoleName::TEACHER . '|' . \App\Constants\RoleName::STUDENT)
        <!-- Boutons 80% et 100% -->
        @if (in_array($currentState, ['not_evaluated', 'auto80', 'eval80', 'auto100', 'eval100']))
            @role(\App\Constants\RoleName::TEACHER)
                <button type="button" class="eval-tab-btn btn {{ $isEvaluated ? 'btn-secondary' : 'btn-outline' }}"
                    data-level="eval80" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-eval80"
                    @if ($isEvaluated) disabled @endif>
                    {{ __('Eval 3/4') }}
                </button>
                <button type="button"
                    class="eval-tab-btn btn {{ isCurrentState('eval100', $currentState) ? 'btn-secondary' : 'btn-outline' }}"
                    data-level="eval100" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-eval100"
                    @if ($isEvaluated) disabled @endif>
                    {{ __('Eval 100%') }}
                </button>
            @endrole

            @role(\App\Constants\RoleName::STUDENT)
                <button type="button" class="eval-tab-btn btn {{ $isAutoEvaluated ? 'btn-primary' : 'btn-outline' }}"
                    data-level="auto80" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-auto80"
                    @if ($isAutoEvaluated) disabled @endif>
                    {{ __('Auto eval 3/4') }}
                </button>
                <button type="button"
                    class="eval-tab-btn btn {{ isCurrentState('auto100', $currentState) ? 'btn-primary' : 'btn-outline' }}"
                    data-level="auto100" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-auto100"
                    @if ($isAutoEvaluated) disabled @endif>
                    {{ __('Auto eval 100%') }}
                </button>
            @endrole

            <!-- Bouton Valider -->
            @if ($canValidate)
                <button type="button" class="eval-tab-btn btn btn-success" id="id-{{ $studentId }}-finish-btn"
                    onclick="finishEvaluation('{{ $studentId }}-btn-')"
                    @if ($currentState === 'not_evaluated') disabled @endif>
                    {{ __('Validate') }}
                </button>
            @endif
        @endif

        <!-- Bouton Terminer ou Modifier -->
        @if ($currentState === 'pending_signature')
            <button type="button" class="eval-tab-btn btn btn-success" id="id-{{ $studentId }}-finish-btn"
                onclick="finishEvaluation('{{ $studentId }}-btn-')">
                {{ __('Finish') }}
            </button>
        @elseif ($currentState === 'completed')
            <button type="button" class="eval-tab-btn btn btn-success" id="id-{{ $studentId }}-validation-btn"
                onclick="validateEvaluation('{{ $studentId }}-btn-')" @if ($currentState === 'not_evaluated') disabled @endif>
                {{ __('Edit') }}
            </button>
        @endif

        <!-- Message pour l'état "completed" -->
        @if ($currentState === 'completed')
            <span class="message">{{ __('Evaluation closed') }}</span>
        @endif

        <!-- Messages d'état suivant -->
        @if (isset($nextStateMessages[$currentState]))
            <span class="next-state-message absolute top-14">
                {{ $nextStateMessages[$currentState] }}
            </span>
        @endif
    @endhasanyrole
</div>
