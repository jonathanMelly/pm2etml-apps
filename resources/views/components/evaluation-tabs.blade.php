<div class="evaluation-tabs flex space-x-6" id="id-{{ $studentId }}-btn">

    @php
        if (!function_exists('isCurrentState')) {
            function isCurrentState($desiredState, $currentState)
            {
                return $desiredState === $currentState;
            }
        }
    @endphp

    @hasanyrole(\App\Constants\RoleName::TEACHER . '|' . \App\Constants\RoleName::STUDENT)
        @role(\App\Constants\RoleName::TEACHER)
            <!-- Évaluation 80 -->
            <button type="button"
                class="eval-tab-btn btn {{ isCurrentState('eval80', optional(optional($hasEval)->getCurrentState())->value) ? 'btn-secondary' : 'btn-outline' }}"
                data-level="eval80" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-eval80">
                {{ __('Evaluation 3/4') }}
            </button>

            <!-- Évaluation 100 -->
            <button type="button"
                class="eval-tab-btn btn {{ isCurrentState('eval100', optional(optional($hasEval)->getCurrentState())->value) ? 'btn-secondary' : 'btn-outline' }}"
                data-level="eval100" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-eval100"
                @if (isCurrentState('eval80', optional(optional($hasEval)->getCurrentState())->value ?? '')) disabled @endif>
                {{ __('Evaluation 100%') }}
            </button>
            @if ($hasEval && $hasEval->getCurrentState())
                <span class="next-state absolute right-5">
                    {{ __('next State: ') . $hasEval->getNextState('teacher')->value }}
                </span>
            @endif
        @endrole

        @role(\App\Constants\RoleName::STUDENT)
            <!-- Auto-évaluation 80 -->
            <button type="button"
                class="eval-tab-btn btn {{ isCurrentState('auto80', optional(optional($hasEval)->getCurrentState())->value) ? 'btn-primary' : 'btn-outline' }}"
                data-level="auto80" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-auto80">
                {{ __('Auto evaluation 3/4') }}
            </button>

            <!-- Auto-évaluation 100 -->
            <button type="button"
                class="eval-tab-btn btn {{ isCurrentState('auto100', optional(optional($hasEval)->getCurrentState())->value) ? 'btn-primary' : 'btn-outline' }}"
                data-level="auto100" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-auto100"
                @if (!isCurrentState('auto80', optional(optional($hasEval)->getCurrentState())->value)) disabled @endif>
                {{ __('Auto evaluation 100%') }}
            </button>
            @if ($hasEval && $hasEval->getCurrentState())
                <span class="next-state absolute right-5">
                    {{ __('Next State: ') . $hasEval->getNextState('student')->value }}
                </span>
            @endif
        @endrole
    @endhasanyrole

    <button type="button" class="eval-tab-btn btn btn-outline btn-success" id="id-{{ $studentId }}-validation-btn"
        onclick="validateEvaluation('{{ $studentId }}-btn-')">{{ __('Validate') }}
    </button>


</div>
