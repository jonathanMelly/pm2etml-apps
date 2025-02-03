<div class="evaluation-tabs flex space-x-6 relative justify-end" id="id-{{ $studentId }}-btn">
    @php
        if (!function_exists('isCurrentState')) {
            function isCurrentState($desiredState, $currentState)
            {
                return $desiredState === $currentState;
            }
        }

        if (!function_exists('getNextStateMessage')) {
            function getNextStateMessage($nextState)
            {
                $msg = [
                    'not_evaluated' => __('Start Evaluation'),
                    'auto80' => __('Auto Eval 3/4'),
                    'eval80' => __('Eval 3/4'),
                    'auto100' => __('Auto Eval 100%'),
                    'eval100' => __('Eval 100%'),
                    'pending_signature' => __('Complete Evaluation'),
                ];
                $nextStateMessage = $msg[$nextState] ?? $nextState;

                return __('What you need to do: :message', ['message' => $nextStateMessage]);
            }
        }

    @endphp

    @hasanyrole(\App\Constants\RoleName::TEACHER . '|' . \App\Constants\RoleName::STUDENT)
        @php
            $currentState = optional(optional($hasEval)->getCurrentState())->value;
            $nextStateTeacher = $hasEval ? $hasEval->getNextState('teacher')->value : null;
            $nextStateStudent = $hasEval ? $hasEval->getNextState('student')->value : null;
        @endphp

        @role(\App\Constants\RoleName::TEACHER)
            <!-- Évaluation 80 -->
            <button type="button"
                class="eval-tab-btn btn {{ isCurrentState('eval80', $currentState) ? 'btn-secondary' : 'btn-outline' }}"
                data-level="eval80" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-eval80">
                {{ __('Eval 3/4') }}
            </button>

            <!-- Évaluation 100 -->
            <button type="button"
                class="eval-tab-btn btn {{ isCurrentState('eval100', $currentState) ? 'btn-secondary' : 'btn-outline' }}"
                data-level="eval100" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-eval100"
                @if (isCurrentState('eval80', $currentState)) disabled @endif>
                {{ __('Eval 100%') }}
            </button>

            <!-- Message d'état suivant pour le professeur -->
            @if ($nextStateTeacher)
                <span class="next-state absolute right-0 top-16" id="id-{{ $studentId }}-nextState-teacher">
                    {{ getNextStateMessage($nextStateTeacher) }}
                </span>
            @endif
        @endrole

        @role(\App\Constants\RoleName::STUDENT)
            <!-- Auto-évaluation 80 -->
            <button type="button"
                class="eval-tab-btn btn {{ isCurrentState('auto80', $currentState) ? 'btn-primary' : 'btn-outline' }}"
                data-level="auto80" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-auto80">
                {{ __('Auto eval 3/4') }}
            </button>

            <!-- Auto-évaluation 100 -->
            <button type="button"
                class="eval-tab-btn btn {{ isCurrentState('auto100', $currentState) ? 'btn-primary' : 'btn-outline' }}"
                data-level="auto100" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-auto100"
                @if (!isCurrentState('auto80', $currentState)) disabled @endif>
                {{ __('Auto eval 100%') }}
            </button>

            <!-- Message d'état suivant pour l'étudiant -->
            @if ($nextStateStudent)
                <span class="next-state absolute right-0 top-16" id="id-{{ $studentId }}-nextState-student">
                    {{ __('What you need to do:') . ' ' . getNextStateMessage($nextStateStudent) }}
                </span>
            @endif
        @endrole
    @endhasanyrole

    <!-- Bouton de validation -->
    <button type="button" class="eval-tab-btn btn btn-outline btn-success" id="id-{{ $studentId }}-validation-btn"
        onclick="validateEvaluation('{{ $studentId }}-btn-')">
        {{ __('Validate') }}
    </button>
</div>
