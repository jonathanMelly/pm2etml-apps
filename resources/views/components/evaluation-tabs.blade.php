<div class="evaluation-tabs flex space-x-6 relative" id="id-{{ $studentId }}-btn">

    @php
        if (!function_exists('isCurrentState')) {
            function isCurrentState($desiredState, $currentState)
            {
                return $desiredState === $currentState;
            }
        }

        function getNextStateMessage($nextState)
        {
            $nextStates = [
                'not_evaluated' => __('Start Evaluation'),
                'auto80' => __('Evaluate 3/4'),
                'eval80' => __('Evaluate 3/4'),
                'auto100' => __('Evaluate 100%'),
                'eval100' => __('Pending Signature'),
                'pending_signature' => __('Complete Evaluation'),
            ];
            $nextStateMessage = $nextStates[$nextState] ?? $nextState;

            return __('What you need to do: :message', ['message' => $nextStateMessage]);
        }

    @endphp

    @hasanyrole(\App\Constants\RoleName::TEACHER . '|' . \App\Constants\RoleName::STUDENT)
        @role(\App\Constants\RoleName::TEACHER)
            <!-- Évaluation 80 -->
            <button type="button"
                class="eval-tab-btn btn {{ isCurrentState('eval80', optional(optional($hasEval)->getCurrentState())->value) ? 'btn-secondary' : 'btn-outline' }}"
                data-level="eval80" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-eval80">
                {{ __('Eval 3/4') }}
            </button>

            <!-- Évaluation 100 -->
            <button type="button"
                class="eval-tab-btn btn {{ isCurrentState('eval100', optional(optional($hasEval)->getCurrentState())->value) ? 'btn-secondary' : 'btn-outline' }}"
                data-level="eval100" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-eval100"
                @if (isCurrentState('eval80', optional(optional($hasEval)->getCurrentState())->value ?? '')) disabled @endif>
                {{ __('Eval 100%') }}
            </button>

            {{-- Display the expected action for the teacher --}}
            @if ($hasEval && $hasEval->getCurrentState())
                <span class="next-state absolute right-5" id="id-{{ $studentId }}-nextState-teacher">
                    {{ getNextStateMessage($hasEval->getNextState('teacher')->value) }}
                </span>
            @endif
        @endrole

        @role(\App\Constants\RoleName::STUDENT)
            <!-- Auto-évaluation 80 -->
            <button type="button"
                class="eval-tab-btn btn {{ isCurrentState('auto80', optional(optional($hasEval)->getCurrentState())->value) ? 'btn-primary' : 'btn-outline' }}"
                data-level="auto80" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-auto80">
                {{ __('Auto eval 3/4') }}
            </button>

            <!-- Auto-évaluation 100 -->
            <button type="button"
                class="eval-tab-btn btn {{ isCurrentState('auto100', optional(optional($hasEval)->getCurrentState())->value) ? 'btn-primary' : 'btn-outline' }}"
                data-level="auto100" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-auto100"
                @if (!isCurrentState('auto80', optional(optional($hasEval)->getCurrentState())->value)) disabled @endif>
                {{ __('Auto eval 100%') }}
            </button>
            @if ($hasEval && $hasEval->getCurrentState())
                <span class="next-state absolute right-5" id="id-{{ $studentId }}-nextState-student">
                    {{ __('What you need to do:') . ' ' . getNextStateMessage($hasEval->getNextState('student')->value) }}
                </span>
            @endif
        @endrole
    @endhasanyrole

    <button type="button" class="eval-tab-btn btn btn-outline btn-success" id="id-{{ $studentId }}-validation-btn"
        onclick="validateEvaluation('{{ $studentId }}-btn-')">{{ __('Validate') }}
    </button>


</div>
