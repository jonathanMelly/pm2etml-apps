@props([
    'status_eval',
    'studentId',
    'hasEval',
    'btnEval80IsOn',
    'btnEval100IsOn',
    'btnAuto80IsOn',
    'btnAuto100IsOn',
])

@php
    // État actuel avec fallback
    $currentState = optional(optional($hasEval)->getCurrentState())->value ?? 'not_evaluated';

    $btnEval80IsOn = in_array($currentState, ['not_evaluated', 'auto80']) && $status_eval != 'eval100';
    $btnEval100IsOn = in_array($currentState, ['eval80', 'auto100', 'eval100']) && $status_eval != 'eval100';

    $btnAuto80IsOn = in_array($currentState, ['not_evaluated']) && $status_eval != 'eval100';
    $btnAuto100IsOn = in_array($currentState, ['auto80', 'eval80', 'auto100']) && $status_eval != 'eval100';

    $canTeacherValidate = in_array($currentState, ['auto80', 'auto100']) && $status_eval !== $currentState;
    $canStudentValidate = in_array($currentState, ['eval80', 'eval100']) && $status_eval !== $currentState;

    $canEdit = $status_eval === 'completed';

    $stateMessages = [
        'not_evaluated' => __("Pas encore d'évaluation"),
        'eval80' => __('Validation de l’évaluation formative requise pour continuer.'),
        'auto80' => __('L’étudiant attend votre validation.'),
        'auto100' => __('En attente de l’évaluation par l’enseignant.'),
        'eval100' => __('En attente de validation par l’étudiant.'),
        'pending_signature' => __('En attente de signature finale.'),
        'completed' => __('Évaluation terminée.'),
    ];

    // Debugging (à enlever en production)
    dump($currentState, $status_eval, $btnEval80IsOn, $btnEval100IsOn, $canTeacherValidate, $canEdit);
@endphp

@hasanyrole(\App\Constants\RoleName::TEACHER . '|' . \App\Constants\RoleName::STUDENT)
    <div class="evaluation-tabs flex space-x-6 relative justify-end" id="id-{{ $studentId }}-btn"
        data-status={{ $status_eval }} data-current-state="{{ $currentState }}"
        data-next-state-teacher="{{ $hasEval ? $hasEval->getNextState('teacher')->value : null }}"
        @role(\App\Constants\RoleName::TEACHER)
            data-btnEval80IsOn="{{ $btnEval80IsOn }}" data-btnEval100IsOn="{{ $btnEval100IsOn }}"
        @else
            data-btnAuto80IsOn="{{ $btnAuto80IsOn }}" data-btnAuto100IsOn="{{ $btnAuto100IsOn }}"
            data-next-state-student="{{ $hasEval ? $hasEval->getNextState('student')->value : null }}"
        @endrole>

        @if (!in_array($status_eval, ['completed']))
            @role(\App\Constants\RoleName::TEACHER)
                <button type="button" class="eval-tab-btn btn {{ $btnEval80IsOn ? 'btn-secondary' : 'btn-outline' }}"
                    data-level="eval80" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-eval80">
                    {{ __('Éval 3/4') }}
                </button>

                <button type="button" class="eval-tab-btn btn {{ $btnEval100IsOn ? 'btn-secondary' : 'btn-outline' }}"
                    data-level="eval100" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-eval100">
                    {{ __('Éval 100%') }}
                </button>
                @if ($canTeacherValidate)
                    <button type="button" class="eval-tab-btn btn btn-success" id="id-{{ $studentId }}-finish-btn"
                        onclick="validateEvaluation('{{ $studentId }}')">
                        {{ __('Valider') }}
                    </button>
                @endif
            @endrole
            @role(\App\Constants\RoleName::STUDENT)
                <button type="button" class="eval-tab-btn btn {{ $btnAuto80IsOn ? 'btn-primary' : 'btn-outline' }}"
                    data-level="auto80" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-auto80">
                    {{ __('Auto éval 3/4') }}
                </button>

                <button type="button" class="eval-tab-btn btn {{ $btnAuto100IsOn ? 'btn-primary' : 'btn-outline' }}"
                    data-level="auto100" onclick="changeTab(this)" id="id-{{ $studentId }}-btn-auto100">
                    {{ __('Auto éval 100%') }}
                </button>


                @if ($canStudentValidate)
                    <button type="button" class="eval-tab-btn btn btn-success" id="id-{{ $studentId }}-finish-btn"
                        onclick="validateEvaluation('{{ $studentId }}')">
                        {{ __('Valider') }}
                    </button>
                @endif
            @endrole
        @endif

        @if ($status_eval === 'eval100')
            <button type="button" class="eval-tab-btn btn btn-success" id="id-{{ $studentId }}-finish-btn"
                onclick="finishEvaluation('{{ $studentId }}', 'eval100')">
                {{ __('Terminer') }}
            </button>
        @endif

        @if ($status_eval === 'pending_signature')
            <button type="button" class="eval-tab-btn btn btn-success" id="id-{{ $studentId }}-finish-btn"
                onclick="finishEvaluation('{{ $studentId }}', 'pending_signature')">
                {{ __('Confirmer') }}
            </button>
        @endif

        @if ($canEdit)
            <button type="button" class="eval-tab-btn btn btn-success" id="id-{{ $studentId }}-validation-btn"
                onclick="editEvaluation('{{ $studentId }}-btn-')">
                {{ __('Modifier') }}
            </button>
        @endif

        @if ($currentState === 'completed')
            <span class="message text-green-500 font-bold">{{ __('Évaluation cloturée.') }}</span>
        @endif

        @if (isset($stateMessages[$currentState]))
            <span class="next-state-message absolute top-14 text-gray-600 text-sm">
                {{ $stateMessages[$currentState] }}
            </span>
        @endif
    @endhasanyrole
</div>
