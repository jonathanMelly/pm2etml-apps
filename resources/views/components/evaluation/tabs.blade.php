@props(['status_eval', 'studentId', 'workflow_state' => null])

@php
    use App\Constants\AssessmentState;
    use App\Constants\AssessmentTiming;
    use App\Constants\AssessmentWorkflowState;
    use App\Constants\RoleName;
    use Illuminate\Support\Facades\Auth;

    $isTeacher = Auth::user()?->hasRole(RoleName::TEACHER);
    $isStudent = Auth::user()?->hasRole(RoleName::STUDENT);

    // État courant (timing prioritaire) + workflow enrichi
    $currentState = $status_eval ?? AssessmentState::NOT_EVALUATED->value;
    $workflow = $workflow_state;

    // Styles
    $teacherClass = 'btn-secondary';
    $studentClass = 'btn-primary';

    // Message de statut (affiché sous les boutons)
    $statusMessage = null;
    if ($workflow) {
        $statusMessage = match ($workflow) {
            AssessmentWorkflowState::WAITING_STUDENT_FORMATIVE->value     => __("En attente de l’auto‑évaluation formative (ELEV‑F)."),
            AssessmentWorkflowState::WAITING_TEACHER_VALIDATION_F->value  => __("Auto‑évaluation formative envoyée — validation enseignant requise (ENS‑F)."),
            AssessmentWorkflowState::FORMATIVE_VALIDATED->value           => __("Évaluation formative validée."),
            AssessmentWorkflowState::WAITING_TEACHER_SUMMATIVE->value     => __("En attente de l’évaluation sommative de l’enseignant (ENS‑S)."),
            AssessmentWorkflowState::TEACHER_SUMMATIVE_DONE->value        => __("Évaluation sommative de l’enseignant effectuée."),
            AssessmentWorkflowState::CLOSED_BY_TEACHER->value             => __("Évaluation clôturée."),
            AssessmentWorkflowState::TEACHER_ACK_FORMATIVE->value         => __('Accusé de réception (formative). Démarrez ENS‑F.'),
            AssessmentWorkflowState::WAITING_TEACHER_VALIDATION_F2->value  => __('Validation de F2 par enseignant requise.'),
            AssessmentWorkflowState::TEACHER_ACK_FORMATIVE2->value         => __('F2 validée par l’enseignant.'),
            AssessmentWorkflowState::TEACHER_FORMATIVE_DONE->value        => __('Formative enseignant effectuée.'),
            AssessmentWorkflowState::WAITING_STUDENT_FORMATIVE2_OPT->value => __('Formative 2 (ELEV‑F2) optionnelle.'),
            default => null,
        };
    } else {
        // Repli simple sur l'état principal
        $statusMessage = match ($currentState) {
            AssessmentState::NOT_EVALUATED->value => $isTeacher
                ? __("En attente de l’auto‑éval formative (ELEV‑F).")
                : __("Commencez l’auto‑éval formative (ELEV‑F)."),
            AssessmentState::AUTO_FORMATIVE->value => $isTeacher
                ? __("Auto‑éval formative à valider (ENS‑F).")
                : __("Auto‑éval formative envoyée."),
            AssessmentState::EVAL_FORMATIVE->value => __("Éval formative validée."),
            AssessmentState::AUTO_FINALE->value    => $isTeacher
                ? __('Auto‑éval F2 élève à valider (ENS‑S).')
                : __('Auto‑éval F2 envoyée.'),
            AssessmentState::EVAL_FINALE->value    => __("Évaluation sommative effectuée (ENS‑S)."),
            AssessmentState::PENDING_SIGNATURE->value => __("Signature en attente."),
            AssessmentState::COMPLETED->value => __("Évaluation finalisée."),
            default => null,
        };
    }
@endphp

@hasanyrole(RoleName::TEACHER . '|' . RoleName::STUDENT)
    <div class="evaluation-tabs flex space-x-4 relative justify-end" id="id-{{ $studentId }}-btn"
        data-status="{{ $status_eval }}" data-current-state="{{ $currentState }}" data-workflow="{{ $workflow ?? '' }}"
        data-role="{{ $isTeacher ? 'teacher' : ($isStudent ? 'student' : '') }}">

        @php
            // Détermine si un bouton "Valider" est attendu pour ce rôle
            $showValidateTeacher = false;
            $showValidateStudent = false;

            if ($workflow) {
                // Cas avec workflow enrichi
                if ($isTeacher) {
                    $showValidateTeacher = in_array($workflow, [
                        AssessmentWorkflowState::WAITING_TEACHER_VALIDATION_F->value,
                        AssessmentWorkflowState::WAITING_TEACHER_VALIDATION_F2->value,
                        AssessmentWorkflowState::WAITING_TEACHER_SUMMATIVE->value,
                        AssessmentWorkflowState::TEACHER_SUMMATIVE_DONE->value,
                    ], true);
                } elseif ($isStudent) {
                    // L'élève peut valider lorsque l'enseignant a fini sa sommative
                    $showValidateStudent = in_array($workflow, [
                        AssessmentWorkflowState::TEACHER_SUMMATIVE_DONE->value,
                    ], true);
                }
            } else {
                // Repli simple sans workflow
                if ($isTeacher) {
                    $showValidateTeacher = in_array($currentState, [
                        AssessmentState::AUTO_FORMATIVE->value,
                        AssessmentState::AUTO_FINALE->value,
                    ], true);
                } elseif ($isStudent) {
                    $showValidateStudent = ($currentState === AssessmentState::PENDING_SIGNATURE->value);
                }
            }
        @endphp

        {{-- ENSEIGNANT --}}
        @role(RoleName::TEACHER)
            @if ($status_eval !== AssessmentState::COMPLETED->value)
                <button type="button"
                    class="eval-tab-btn btn {{ $teacherClass }} {{ $currentState === AssessmentState::EVAL_FORMATIVE->value ? '' : 'btn-outline' }}"
                    data-level="{{ AssessmentTiming::EVAL_FORMATIVE }}" data-student-id="{{ $studentId }}"
                    title="{{ AssessmentTiming::labels()[AssessmentTiming::EVAL_FORMATIVE] }}" onclick="changeTab(this)">
                    ENS-F1
                </button>

                <button type="button"
                    class="eval-tab-btn btn {{ $teacherClass }} {{ $currentState === AssessmentState::EVAL_FINALE->value ? '' : 'btn-outline' }}"
                    data-level="{{ AssessmentTiming::EVAL_FINALE }}" data-student-id="{{ $studentId }}"
                    title="{{ AssessmentTiming::labels()[AssessmentTiming::EVAL_FINALE] }}" onclick="changeTab(this)">
                    ENS-S
                </button>
                @if ($showValidateTeacher)
                    <button type="button" class="eval-tab-btn btn btn-success"
                        onclick="validateEvaluation('{{ $studentId }}','{{ $currentState }}',this)">
                        {{ $workflow === \App\Constants\AssessmentWorkflowState::TEACHER_SUMMATIVE_DONE->value ? __('Terminer') : __('Valider') }}
                    </button>
                @endif
            @endif
        @endrole

        {{-- ÉTUDIANT --}}
        @role(RoleName::STUDENT)
            @if ($status_eval !== AssessmentState::COMPLETED->value)
                <button type="button"
                    class="eval-tab-btn btn {{ $studentClass }} {{ $currentState === AssessmentState::AUTO_FORMATIVE->value ? '' : 'btn-outline' }}"
                    data-level="{{ AssessmentTiming::AUTO_FORMATIVE }}" data-student-id="{{ $studentId }}"
                    title="{{ AssessmentTiming::labels()[AssessmentTiming::AUTO_FORMATIVE] }}" onclick="changeTab(this)">
                    {{ AssessmentTiming::shortLabels()[AssessmentTiming::AUTO_FORMATIVE] }}
                </button>

                <button type="button"
                    class="eval-tab-btn btn {{ $studentClass }} {{ $currentState === AssessmentState::AUTO_FINALE->value ? '' : 'btn-outline' }}"
                    data-level="{{ AssessmentTiming::AUTO_FINALE }}" data-student-id="{{ $studentId }}"
                    title="{{ AssessmentTiming::labels()[AssessmentTiming::AUTO_FINALE] }}" onclick="changeTab(this)">
                    {{ AssessmentTiming::shortLabels()[AssessmentTiming::AUTO_FINALE] }}
                </button>
                @if ($showValidateStudent)
                    <button type="button" class="eval-tab-btn btn btn-success"
                        onclick="validateEvaluation('{{ $studentId }}','{{ $currentState }}',this)">
                        {{ __('Valider') }}
                    </button>
                @endif
            @endif
        @endrole

        {{-- Aide contextuelle (workflow si dispo) --}}
        @php
            $hintMessage = null;
            if ($workflow) {
                if ($isStudent) {
                    $hintMessage = match ($workflow) {
                        AssessmentWorkflowState::WAITING_STUDENT_FORMATIVE->value     => __('💡 Cliquez sur “ELEV‑F” pour démarrer votre auto‑évaluation formative.'),
                        AssessmentWorkflowState::WAITING_TEACHER_VALIDATION_F->value  => __('💡 En attente de validation de l’enseignant (ENS‑F).'),
                        AssessmentWorkflowState::TEACHER_ACK_FORMATIVE->value         => __('💡 L’enseignant va réaliser l’évaluation formative (ENS‑F).'),
                        AssessmentWorkflowState::TEACHER_FORMATIVE_DONE->value        => __('💡 Poursuivez vers F2 (optionnel) si demandé.'),
                        AssessmentWorkflowState::WAITING_TEACHER_VALIDATION_F2->value  => __('💡 Validez la F2 (clic sur Valider).'),
                        AssessmentWorkflowState::TEACHER_ACK_FORMATIVE2->value         => __('💡 Réalisez l’évaluation sommative (ENS‑S).'),
                        AssessmentWorkflowState::WAITING_STUDENT_FORMATIVE2_OPT->value => __('💡 Vous pouvez faire ELEV‑F2 (optionnel).'),
                        AssessmentWorkflowState::FORMATIVE_VALIDATED->value           => __('💡 Choisissez ELEV‑F2 (optionnel) si demandé.'),
                        AssessmentWorkflowState::WAITING_TEACHER_SUMMATIVE->value     => __('💡 En attente de validation de l’enseignant (ENS‑S).'),
                        AssessmentWorkflowState::TEACHER_SUMMATIVE_DONE->value        => __('💡 Vérifiez l’évaluation de l’enseignant.'),
                        default => null,
                    };
                } elseif ($isTeacher) {
                    $hintMessage = match ($workflow) {
                        AssessmentWorkflowState::WAITING_STUDENT_FORMATIVE->value     => __('💡 Attente que l’élève démarre l’auto‑évaluation formative.'),
                        AssessmentWorkflowState::WAITING_TEACHER_VALIDATION_F->value  => __('💡 Validez l’évaluation formative (ENS‑F).'),
                        AssessmentWorkflowState::TEACHER_ACK_FORMATIVE->value         => __('💡 Démarrez l’évaluation formative (ENS‑F).'),
                        AssessmentWorkflowState::TEACHER_FORMATIVE_DONE->value        => __('💡 Invitez l’élève à ELEV‑F2 (optionnel) ou à poursuivre.'),
                        AssessmentWorkflowState::WAITING_STUDENT_FORMATIVE2_OPT->value => __('💡 Proposez ELEV‑F2 (optionnel).'),
                        AssessmentWorkflowState::FORMATIVE_VALIDATED->value           => __('💡 Invitez l’élève à ELEV‑F2 (optionnel) ou à poursuivre.'),
                        AssessmentWorkflowState::WAITING_TEACHER_SUMMATIVE->value     => __('💡 Réalisez/validez l’évaluation sommative (ENS‑S).'),
                        AssessmentWorkflowState::TEACHER_SUMMATIVE_DONE->value        => __('💡 Cliquez sur “Terminer” pour clôturer.'),
                        AssessmentWorkflowState::CLOSED_BY_TEACHER->value             => null,
                        default => null,
                    };
                }
            } else {
                if ($currentState === AssessmentState::NOT_EVALUATED->value) {
                    $hintMessage = __('💡 Cliquez sur un type d’évaluation pour commencer');
                } elseif ($isStudent) {
                    $hintMessage = match ($currentState) {
                        AssessmentState::AUTO_FORMATIVE->value    => __('💡 En attente de validation de l’enseignant (ENS‑F).'),
                        AssessmentState::EVAL_FORMATIVE->value    => __('💡 Sélectionnez l’auto‑évaluation sommative (ELEV‑S).'),
                        AssessmentState::AUTO_FINALE->value       => __('💡 En attente de validation de l’enseignant (ENS‑S).'),
                        AssessmentState::EVAL_FINALE->value       => __('💡 En attente de signature.'),
                        AssessmentState::PENDING_SIGNATURE->value => __('💡 Signez l’évaluation.'),
                        default => null,
                    };
                } elseif ($isTeacher) {
                    $hintMessage = match ($currentState) {
                        AssessmentState::AUTO_FORMATIVE->value    => __('💡 Validez l’évaluation formative (ENS‑F).'),
                        AssessmentState::EVAL_FORMATIVE->value    => __('💡 Invitez l’élève à ELEV‑F2 (optionnel) ou à poursuivre.'),
                        AssessmentState::AUTO_FINALE->value       => __('💡 Réalisez l’évaluation sommative (ENS‑S).'),
                        AssessmentState::EVAL_FINALE->value       => __('💡 Cliquez sur “Terminer” pour clôturer.'),
                        default => null,
                    };
                }
            }
        @endphp
        @if ($hintMessage && $currentState !== AssessmentState::COMPLETED->value)
            <div class="absolute -top-10 -right-3 bg-amber-50 text-amber-800 text-sm font-medium border border-amber-300 px-3 py-1 rounded-md animate-pulse"
                id="help-msg-{{ $studentId }}">
                {{ $hintMessage }}
            </div>
        @endif

        {{-- STATUT --}}
        @if ($statusMessage)
            <span class="next-state-message absolute top-14 text-cyan-700 font-medium bg-cyan-50 px-2 py-0.5 rounded">
                {{ __('Statut : ') }} {{ $statusMessage }}
            </span>
        @endif
    </div>
@endhasanyrole




