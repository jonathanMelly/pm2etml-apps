<!-- resources/views/components/evaluation-tabs.blade.php -->
<div class="evaluation-tabs flex space-x-6" id="id-{{ $studentId }}-btn">
    @hasanyrole(\App\Constants\RoleName::TEACHER . '|' . \App\Constants\RoleName::STUDENT)
        @role(\App\Constants\RoleName::TEACHER)
            <!-- Évaluation 80 -->
            <button type="button" class="eval-tab-btn btn {{ $hasEval80 ? 'btn-secondary' : 'btn-outline' }}" data-level="eval80"
                onclick="changeTab(this)" id="id-{{ $studentId }}-btn-eval80">
                {{ __('fullEvaluation.eval80') }}
            </button>

            <!-- Évaluation 100 -->
            <button type="button" class="eval-tab-btn btn {{ $hasEval100 ? 'btn-secondary' : 'btn-outline' }}"
                data-level="eval100" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-eval100"
                {{ $hasEval80 ? 'disabled' : '' }}>
                {{ __('Evaluation 100%') }}
            </button>
        @endrole

        @role(\App\Constants\RoleName::STUDENT)
            <!-- Auto-évaluation 80 -->
            <button type="button" class="eval-tab-btn btn {{ $hasAuto80 ? 'btn-primary' : 'btn-outline' }}" data-level="auto80"
                onclick="changeTab(this)" id="id-{{ $studentId }}-btn-auto80">{{ __('Auto evaluation 3/4') }}
            </button>
            <!-- Auto-évaluation 100 -->
            <button type="button" class="eval-tab-btn btn {{ $hasAuto100 ? 'btn-primary' : 'btn-outline' }}"
                data-level="auto100" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-auto100"
                {{ !$hasAuto80 ? 'disabled' : '' }}>
                {{ __('Auto evaluation 100%') }}
            </button>
        @endrole
    @endhasanyrole

    <button type="button" class="eval-tab-btn btn btn-outline btn-success" id="id-{{ $studentId }}-validation-btn"
        onclick="validateEvaluation('{{ $studentId }}-btn-')">{{ __('Validate') }}
    </button>
</div>
