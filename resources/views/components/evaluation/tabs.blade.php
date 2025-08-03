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
    use App\Constants\AssessmentState;
    use App\Constants\RoleName;
    use Illuminate\Support\Facades\Auth;

    // Récupérer l’état actuel avec fallback
    if ($status_eval !== AssessmentState::COMPLETED->value) {
        $currentState = optional(optional($hasEval)->getCurrentState())->value ?? AssessmentState::NOT_EVALUATED->value;
    } else {
        $currentState = AssessmentState::COMPLETED->value;
    }

    $isTeacher = Auth::user()?->hasRole(RoleName::TEACHER);
    $isStudent = Auth::user()?->hasRole(RoleName::STUDENT);

    // Boutons activés selon état et statut
    $btnEval80IsOn = in_array($currentState, [
        AssessmentState::NOT_EVALUATED->value,
        AssessmentState::AUTO80->value,
    ]) && $status_eval !== AssessmentState::EVAL100->value;

    $btnEval100IsOn = in_array($currentState, [
        AssessmentState::EVAL80->value,
        AssessmentState::AUTO100->value,
        AssessmentState::EVAL100->value,
    ]) && $status_eval !== AssessmentState::EVAL100->value;

    $btnAuto80IsOn = in_array($currentState, [
        AssessmentState::NOT_EVALUATED->value,
    ]) && $status_eval !== AssessmentState::EVAL100->value;

    $btnAuto100IsOn = in_array($currentState, [
        AssessmentState::AUTO80->value,
        AssessmentState::EVAL80->value,
        AssessmentState::AUTO100->value,
    ]) && $status_eval !== AssessmentState::EVAL100->value;

    // Permissions pour validation
    $canTeacherValidate = in_array($currentState, [
        AssessmentState::AUTO80->value,
        AssessmentState::AUTO100->value,
    ]) && $status_eval !== $currentState;

    $canStudentValidate = in_array($currentState, [
        AssessmentState::EVAL80->value,
        AssessmentState::EVAL100->value,
    ]) && $status_eval !== $currentState;

    $canEdit = $status_eval === AssessmentState::COMPLETED->value && $currentState === AssessmentState::COMPLETED->value;

    // Messages selon état
    $stateMessages = match ($currentState) {
        AssessmentState::NOT_EVALUATED->value => $isTeacher ? __('Auto-éval formative en attente.') : __('Commencez votre auto-éval.'),
        AssessmentState::AUTO80->value => match (true) {
            $isTeacher && $canTeacherValidate => __('Validez l’auto-éval 80%.'),
            $isTeacher => __('Éval formative à faire.'),
            $isStudent && $status_eval === AssessmentState::AUTO80->value => __('Auto-éval validée.'),
            $isStudent => __('Auto-éval envoyée. En attente.'),
            default => __('Auto-éval formative.'),
        },
        AssessmentState::EVAL80->value => match (true) {
            $isStudent && $canStudentValidate => __('Validez l’éval formative.'),
            $isStudent => __('Auto-éval finale à faire.'),
            $isTeacher && $status_eval === AssessmentState::EVAL80->value => __('Auto-eval finale / Eval sommative ?'),
            $isTeacher => __('Éval formative envoyée.'),
            default => __('Éval formative.'),
        },
        AssessmentState::AUTO100->value => match (true) {
            $isTeacher && $canTeacherValidate => __('Validez l’auto-éval finale.'),
            $isTeacher => __('Auto-éval finale validée / faite l eval sommative.'),
            $isStudent && $status_eval === AssessmentState::AUTO100->value => __('Auto-éval finale validée.'),
            $isStudent => __('Auto-éval finale envoyée. En attente.'),
            default => __('Auto-éval finale.'),
        },
        AssessmentState::EVAL100->value => match (true) {
            $isStudent && $canStudentValidate => __('Validez l’éval finale.'),
            $isStudent => __('Éval finale en attente.'),
            $isTeacher && $status_eval === AssessmentState::EVAL100->value => __('Éval finale validée.'),
            $isTeacher => __('Éval finale complétée.'),
            default => __('Éval finale.'),
        },
        AssessmentState::PENDING_SIGNATURE->value => __('Confirmer la validation de l\'évaluation'),
        AssessmentState::COMPLETED->value => __('Évaluation terminée ✅'),
        default => __('État inconnu.'),
    };

    // Récupérer prudemment les prochains états
    $nextStateTeacher = null;
    $nextStateStudent = null;
    if ($hasEval) {
        $nextTeacher = $hasEval->getNextState(RoleName::TEACHER);
        $nextStudent = $hasEval->getNextState(RoleName::STUDENT);
        $nextStateTeacher = $nextTeacher ? $nextTeacher->value : null;
        $nextStateStudent = $nextStudent ? $nextStudent->value : null;
    }

    // DEBUG (désactiver en prod)
/*    dump([
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
    */
@endphp

@hasanyrole(RoleName::TEACHER . '|' . RoleName::STUDENT)
    <div class="evaluation-tabs flex space-x-6 relative justify-end" id="id-{{ $studentId }}-btn"
        data-status="{{ $status_eval }}"
        data-current-state="{{ $currentState }}"
        data-next-state-teacher="{{ $nextStateTeacher }}"
        @role(RoleName::TEACHER)
            data-btnEval80IsOn="{{ $btnEval80IsOn }}"
            data-btnEval100IsOn="{{ $btnEval100IsOn }}"
        @else
            data-btnAuto80IsOn="{{ $btnAuto80IsOn }}"
            data-btnAuto100IsOn="{{ $btnAuto100IsOn }}"
            data-next-state-student="{{ $nextStateStudent }}"
        @endrole
    >

        @if (!in_array($status_eval, [AssessmentState::COMPLETED->value]))
            @role(RoleName::TEACHER)
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
                        onclick="validateEvaluation('{{ $studentId }}','{{ $currentState }}',this)">
                        {{ __('Valider') }}
                    </button>
                @endif
            @endrole

            @role(RoleName::STUDENT)
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
                        onclick="validateEvaluation('{{ $studentId }}','{{ $currentState }}',this)">
                        {{ __('Valider') }}
                    </button>
                @endif
            @endrole
        @endif

        @if ($status_eval === AssessmentState::PENDING_SIGNATURE->value)
            <button type="button" class="eval-tab-btn btn btn-success" id="id-{{ $studentId }}-finish-btn"
                onclick="finishEvaluation('{{ $studentId }}', '{{ AssessmentState::EVAL100->value }}')">
                {{ __('Terminer') }}
            </button>
        @endif

        @if ($status_eval === AssessmentState::EVAL100->value)
            <button type="button" class="eval-tab-btn btn btn-warning" id="id-{{ $studentId }}-finish-btn"
                onclick="finishEvaluation('{{ $studentId }}', '{{ AssessmentState::PENDING_SIGNATURE->value }}')">
                {{ __('Confirmer') }}
            </button>
        @endif

        @if ($canEdit)
            <button type="button" class="eval-tab-btn btn btn-success" id="id-{{ $studentId }}-validation-btn"
                onclick="editEvaluation('{{ $studentId }}-btn-')">
                {{ __('Modifier') }}
            </button>
            <button type="button" class="btn btn-secondary" onclick="printSection(this)"
                data-print-id="student_id-{{ $studentId }}">
                {{ __('Imprimer') }}
            </button>
        @endif

        @if ($stateMessages)
            <span class="next-state-message absolute top-14 text-cyan-700 font-medium bg-cyan-50 px-2 py-0.5 rounded">
                Statut : {{ $stateMessages }}
            </span>
        @endif
    </div>
@endhasanyrole
