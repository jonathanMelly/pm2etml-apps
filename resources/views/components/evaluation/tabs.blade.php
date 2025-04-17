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
    use Illuminate\Support\Facades\Auth;

    // État actuel avec fallback
    $currentState = optional(optional($hasEval)->getCurrentState())->value ?? 'not_evaluated';

    $isTeacher = Auth::user()?->hasRole(\App\Constants\RoleName::TEACHER);
    $isStudent = Auth::user()?->hasRole(\App\Constants\RoleName::STUDENT);

    $btnEval80IsOn = in_array($currentState, ['not_evaluated', 'auto80']) && $status_eval != 'eval100';
    $btnEval100IsOn = in_array($currentState, ['eval80', 'auto100', 'eval100']) && $status_eval != 'eval100';

    $btnAuto80IsOn = in_array($currentState, ['not_evaluated']) && $status_eval != 'eval100';
    $btnAuto100IsOn = in_array($currentState, ['auto80', 'eval80', 'auto100']) && $status_eval != 'eval100';

    $canTeacherValidate = in_array($currentState, ['auto80', 'auto100']) && $status_eval !== $currentState;
    $canStudentValidate = in_array($currentState, ['eval80', 'eval100']) && $status_eval !== $currentState;

    $canEdit = $status_eval === 'completed';

    $stateMessages = match ($currentState) {
        'not_evaluated' => $isTeacher ? __('Auto-éval formative en attente.') : __('Commencez votre auto-éval.'),

        'auto80' => match (true) {
            $isTeacher && $canTeacherValidate => __('Validez l’auto-éval 80%.'),
            $isTeacher => __('Éval formative à faire.'),
            $isStudent && $status_eval === 'auto80' => __('Auto-éval validée.'),
            $isStudent => __('Auto-éval envoyée. En attente.'),
            default => __('Auto-éval formative.'),
        },

        'eval80' => match (true) {
            $isStudent && $canStudentValidate => __('Validez l’éval formative.'),
            $isStudent => __('Auto-éval finale à faire.'),
            $isTeacher && $status_eval === 'eval80' => __('Auto-eval finale / Eval sommative ?'),
            $isTeacher => __('éval formative envoyée.'),
            default => __('Éval formative.'),
        },

        'auto100' => match (true) {
            $isTeacher && $canTeacherValidate => __('Validez l’auto-éval finale.'),
            $isTeacher => __('Auto-éval finale validée / faite l eval sommative.'),
            $isStudent && $status_eval === 'auto100' => __('Auto-éval finale validée.'),
            $isStudent => __('Auto-éval finale envoyée. En attente.'),
            default => __('Auto-éval finale.'),
        },

        'eval100' => match (true) {
            $isStudent && $canStudentValidate => __('Validez l’éval finale.'),
            $isStudent => __('Éval finale en attente.'),
            $isTeacher && $status_eval === 'eval100' => __('Éval finale validée.'),
            $isTeacher => __('Éval finale complétée.'),
            default => __('Éval finale.'),
        },

        'pending_signature' => $isTeacher ? __('Signature finale en attente.') : __('À signer pour terminer.'),

        'completed' => __('Évaluation terminée ✅'),

        default => __('État inconnu.'),
    };

    // Debug (désactiver en prod)
    dump([
        '👤Rôle' => $isTeacher ? '👨 Enseignant' : ($isStudent ? '🎓 Étudiant' : '❓ Inconnu'),
        '🔄État actuel de l\'éval' => $currentState,
        '🧭Message associé à l\'état' => $stateMessages ?? '—',
        '📌Statut actuel enregistré' => $status_eval,

        '🎯 Actions disponibles' => [
            '🎓: Auto 3/4 dispo ?' => $btnAuto80IsOn ? '✅ Oui' : '❌ Non',
            '🎓: Auto 100% dispo ?' => $btnAuto100IsOn ? '✅ Oui' : '❌ Non',
            '👨: Eval 3/4 dispo ?' => $btnEval80IsOn ? '✅ Oui' : '❌ Non',
            '👨: Eval 100% dispo ?' => $btnEval100IsOn ? '✅ Oui' : '❌ Non',
        ],

        '🔐 Permissions' => [
            '👨 Validation prof possible ?' => $canTeacherValidate ? '✅ Oui' : '❌ Non',
            '🎓 Validation élève possible ?' => $canStudentValidate ? '✅ Oui' : '❌ Non',
            '✏️ Édition autorisée ?' => $canEdit ? '✅ Oui' : '❌ Non',
        ],
    ]);

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

        @if ($stateMessages)
            <span class="next-state-message absolute top-14 text-gray-600 text-sm">
                {{ $stateMessages }}
            </span>
        @endif
    @endhasanyrole
</div>
