# Définition des États

- **NotEvaluated** : Début du flux, aucune évaluation n'a été effectuée.
- **Student80Evaluated** : L'étudiant a complété l'évaluation à 80% (facultatif).
- **Teacher80Evaluated** : L'enseignant a complété l'évaluation à 80% (obligatoire).
- **Student100Evaluated** : L'étudiant a complété l'évaluation à 100% (facultatif).
- **Teacher100Evaluated** : L'enseignant a complété l'évaluation à 100% (obligatoire).
- **EvaluationCompleted** : Toutes les étapes obligatoires ont été complétées, l'évaluation est terminée.

## Transitions

| État actuel             | Action/Événement                 | Nouvel État           | Condition   |
|-------------------------|----------------------------------|-----------------------|-------------|
| NotEvaluated            | Étudiant complète eval80         | Student80Evaluated    | -           |
| NotEvaluated            | Enseignant complète eval80       | Teacher80Evaluated    | -           |
| Student80Evaluated      | Enseignant complète eval80       | Teacher80Evaluated    | -           |
| Teacher80Evaluated      | Étudiant complète eval100        | Student100Evaluated   | -           |
| Teacher80Evaluated      | Enseignant complète eval100      | Teacher100Evaluated   | -           |
| Student100Evaluated     | Enseignant complète eval100      | Teacher100Evaluated   | -           |
| Teacher100Evaluated     | Toutes les étapes obligatoires sont finies | EvaluationCompleted  | -           |

## Classe `EvaluationStateMachine`

```php
class EvaluationStateMachine
{
    private string $currentState = 'NotEvaluated';

    // Transitions autorisées entre états
    private const TRANSITIONS = [
        'NotEvaluated' => [
            'studentEval80' => 'Student80Evaluated',
            'teacherEval80' => 'Teacher80Evaluated',
        ],
        'Student80Evaluated' => [
            'teacherEval80' => 'Teacher80Evaluated',
        ],
        'Teacher80Evaluated' => [
            'studentEval100' => 'Student100Evaluated',
            'teacherEval100' => 'Teacher100Evaluated',
        ],
        'Student100Evaluated' => [
            'teacherEval100' => 'Teacher100Evaluated',
        ],
        'Teacher100Evaluated' => [
            'completeEvaluation' => 'EvaluationCompleted',
        ],
    ];

    // Actions obligatoires pour terminer l'évaluation
    private const MANDATORY_ACTIONS = [
        'teacherEval80',
        'teacherEval100',
    ];

    // Actions accomplies
    private array $completedActions = [];

    /**
     * Effectuer une transition
     */
    public function transition(string $action): void
    {
        $transitions = self::TRANSITIONS[$this->currentState] ?? [];

        if (isset($transitions[$action])) {
            $this->currentState = $transitions[$action];
            $this->completedActions[] = $action;
        } else {
            throw new Exception("Transition invalide depuis {$this->currentState} avec action : $action");
        }
    }

    /**
     * Vérifier si toutes les actions obligatoires ont été effectuées
     */
    private function areMandatoryActionsCompleted(): bool
    {
        foreach (self::MANDATORY_ACTIONS as $mandatory) {
            if (!in_array($mandatory, $this->completedActions, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Valider la complétion de l'évaluation
     */
    public function completeEvaluation(): void
    {
        if ($this->areMandatoryActionsCompleted()) {
            $this->currentState = 'EvaluationCompleted';
        } else {
            throw new Exception("Toutes les étapes obligatoires ne sont pas terminées.");
        }
    }

    public function getState(): string
    {
        return $this->currentState;
    }
}

$machine = new EvaluationStateMachine();

try {
    $machine->transition('studentEval80');
    echo $machine->getState(); // Student80Evaluated

    $machine->transition('teacherEval80');
    echo $machine->getState(); // Teacher80Evaluated

    $machine->transition('teacherEval100');
    echo $machine->getState(); // Teacher100Evaluated

    $machine->transition('completeEvaluation');
    echo $machine->getState(); // EvaluationCompleted
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
