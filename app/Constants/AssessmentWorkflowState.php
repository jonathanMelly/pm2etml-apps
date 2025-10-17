<?php

namespace App\Constants;

/**
 * État métier enrichi (workflow) calculé à partir des timings existants
 * et, si disponible, du statut global de l'évaluation (signature, clôture).
 *
 * Ces valeurs ne sont pas stockées telles quelles en base — elles sont dérivées
 * des enregistrements dans `assessments` (timing: autoFormative, evalFormative, autoFinale, evalFinale)
 * et du champ `status` éventuel de WorkerContractAssessment.
 */
enum AssessmentWorkflowState: string
{
    // Formative
    case WAITING_STUDENT_FORMATIVE     = 'waiting_student_formative';
    case WAITING_TEACHER_VALIDATION_F  = 'waiting_teacher_validation_f';
    case TEACHER_ACK_FORMATIVE         = 'teacher_ack_formative';
    case TEACHER_FORMATIVE_DONE        = 'teacher_formative_done';
    case WAITING_STUDENT_VALIDATION_F  = 'waiting_student_validation_f';
    case FORMATIVE_VALIDATED           = 'formative_validated'; // phase formative close

    // Summative (élève optionnel, enseignant obligatoire)
    case WAITING_STUDENT_FORMATIVE2_OPT = 'waiting_student_formative2_optional';
    case WAITING_TEACHER_VALIDATION_F2  = 'waiting_teacher_validation_f2';
    case TEACHER_ACK_FORMATIVE2         = 'teacher_ack_formative2';
    case WAITING_TEACHER_SUMMATIVE     = 'waiting_teacher_summative';
    case TEACHER_SUMMATIVE_DONE        = 'teacher_summative_done';
    case SUMMATIVE_VALIDATED           = 'summative_validated';
    case CLOSED_BY_TEACHER             = 'closed_by_teacher';
}
