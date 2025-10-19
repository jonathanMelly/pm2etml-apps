<?php

namespace App\Http\Controllers;

use App\Constants\RoleName;
use App\Models\WorkerContract;
use App\Models\WorkerContractDayLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Enums\RequiredTimeUnit;
use Illuminate\Support\Str;

class WorkerContractJournalController extends Controller
{
    /**
     * Affiche le journal du contrat et, selon le rôle, le formulaire de saisie (élève) ou consultation (prof).
     */
    public function index(Request $request, WorkerContract $workerContract)
    {
        $user = auth()->user();

        $this->authorizeAccess($user->id, $workerContract);

        $logs = $workerContract->dayLogs()->orderByDesc('date')->get();
        $isTeacher = $user->hasRole(RoleName::TEACHER);
        $isStudent = $user->hasRole(RoleName::STUDENT);

        // Totaux périodes allouées vs utilisées (+ 20% max)
        $allocated = (int) round($workerContract->getAllocatedTime(RequiredTimeUnit::PERIOD));
        $allowedMax = (int) floor($allocated * 1.2);
        $used = (int) $workerContract->dayLogs()->sum('periods_count');
        $remaining = max(0, $allowedMax - $used);

        // Suggestions d'appréciations les plus probables pour l'enseignant
        $teacherSuggestions = [];
        if ($isTeacher) {
            $groups = config('journal.appreciations', []);
            $phrases = collect($groups)->flatten()->filter()->values()->all();
            if (!empty($phrases)) {
                $jdId = optional($workerContract->contract)->job_definition_id;
                $groupNameId = optional($workerContract->groupName)->id;
                $recentLogs = WorkerContractDayLog::query()
                    ->whereNotNull('appreciation')
                    ->whereHas('workerContract.contract.clients', fn($q) => $q->where('users.id', $user->id))
                    ->where(function($q) use ($jdId, $groupNameId) {
                        // Suggestions limitées au même projet (jobDefinition) ou même classe (groupName)
                        $q->whereHas('workerContract.contract', function($qq) use ($jdId){
                            if ($jdId) { $qq->where('job_definition_id', $jdId); }
                        })
                        ->orWhereHas('workerContract.group', function($qq) use ($groupNameId){
                            if ($groupNameId) { $qq->where('group_name_id', $groupNameId); }
                        });
                    })
                    ->latest()->limit(500)->get(['appreciation']);

                $counts = [];
                foreach ($phrases as $p) { $counts[$p] = 0; }
                foreach ($recentLogs as $logRow) {
                    $txt = (string) $logRow->appreciation;
                    foreach ($phrases as $p) {
                        if ($p !== '' && Str::contains($txt, $p)) { $counts[$p]++; }
                    }
                }
                arsort($counts);
                $teacherSuggestions = collect($counts)->filter(fn($c) => $c > 0)->keys()->take(5)->values()->all();
            }
        }

        return view('journal.index', compact('workerContract', 'logs', 'isTeacher', 'isStudent', 'allocated', 'allowedMax', 'used', 'remaining', 'teacherSuggestions'));
    }

    /**
     * Enregistre un journal de journée pour un élève.
     */
    public function store(Request $request, WorkerContract $workerContract): RedirectResponse
    {
        $user = auth()->user();
        $this->authorizeStudent($user->id, $workerContract);

        $data = $request->validate([
            'date' => ['required', 'date'],
            'periods_count' => ['required', 'integer', 'min:1', 'max:6'],
            'precision_minutes' => ['required', 'integer', 'in:3,5,15'],
            'student_notes' => ['nullable', 'string'],
            'periods' => ['required', 'array'],
        ]);

        // Validation fine des périodes en fonction de la précision
        $precision = (int) $data['precision_minutes'];
        $periods = $data['periods'];

        // Attendu: index 1..periods_count; chaque élément: [ 'lines' => array<string>, 'note' => string|null ]
        $validatedPeriods = [];
        $maxChunks = intdiv(45, $precision); // lines per period

        for ($i = 1; $i <= (int)$data['periods_count']; $i++) {
            $entry = $periods[$i] ?? null;
            if (!is_array($entry)) {
                return back()->withErrors(["periods.$i" => __('Missing period entry')])->withInput();
            }
            $note = $entry['note'] ?? null;
            $lines = $entry['lines'] ?? [];
            if (!is_array($lines)) {
                return back()->withErrors(["periods.$i.lines" => __('Format invalide')])->withInput();
            }
            // Normaliser chaque ligne: accepte soit string, soit {type,text}
            $normalizedLines = [];
            foreach ($lines as $line) {
                if (is_array($line)) {
                    $normalizedLines[] = [
                        'type' => $line['type'] ?? null,
                        'text' => $line['text'] ?? '',
                    ];
                } else {
                    $normalizedLines[] = [
                        'type' => null,
                        'text' => (string)$line,
                    ];
                }
            }
            // on n'oblige pas à remplir les lignes, mais on force la durée à 45'
            $minutes = 45; // par contrainte
            $validatedPeriods[] = [
                'index' => $i,
                'chunks' => $maxChunks,
                'minutes' => $minutes,
                'note' => $note,
                'lines' => $normalizedLines,
            ];
        }

        try {
            // Contrôle du plafond de périodes (allouées +20%)
            $allocated = (int) round($workerContract->getAllocatedTime(RequiredTimeUnit::PERIOD));
            $allowedMax = (int) floor($allocated * 1.2);
            $used = (int) $workerContract->dayLogs()->sum('periods_count');
            $existingForDate = (int) optional($workerContract->dayLogs()->whereDate('date', $data['date'])->first())->periods_count;
            $newTotal = $used - $existingForDate + (int)$data['periods_count'];
            if ($newTotal > $allowedMax) {
                return back()->withErrors([
                    'periods_count' => __('Le total des périodes (:total) dépasse la limite autorisée (:max).', ['total' => $newTotal, 'max' => $allowedMax])
                ])->withInput();
            }

            WorkerContractDayLog::updateOrCreate(
                [
                    'worker_contract_id' => $workerContract->id,
                    'date' => $data['date'],
                ],
                [
                    'periods_count' => (int)$data['periods_count'],
                    'precision_minutes' => $precision,
                    'student_notes' => $data['student_notes'] ?? null,
                    'periods' => $validatedPeriods,
                ]
            );

            return back()->with('success', __('Journal enregistré.'));
        } catch (\Throwable $e) {
            Log::error('Erreur enregistrement journal', ['error' => $e->getMessage()]);
            return back()->withErrors(['general' => __('Erreur durant la sauvegarde')])->withInput();
        }
    }

    /**
     * Mise à jour de l’appréciation par l’enseignant.
     */
    public function updateAppreciation(Request $request, WorkerContractDayLog $dayLog): RedirectResponse
    {
        $user = auth()->user();
        // Autoriser uniquement le prof lié au contrat
        $this->authorizeTeacher($user->id, $dayLog->workerContract);

        $validated = $request->validate([
            'appreciation' => ['nullable', 'string']
        ]);

        $dayLog->appreciation = $validated['appreciation'] ?? null;
        $dayLog->save();

        return back()->with('success', __('Appréciation enregistrée.'));
    }

    private function authorizeAccess(int $userId, WorkerContract $wc): void
    {
        $this->authorizeStudentOrTeacher($userId, $wc);
    }

    private function authorizeStudent(int $userId, WorkerContract $wc): void
    {
        if ($wc->groupMember?->user_id !== $userId) {
            abort(403);
        }
    }

    private function authorizeTeacher(int $userId, WorkerContract $wc): void
    {
        $clients = $wc->contract?->clients?->pluck('id') ?? collect();
        if (!$clients->contains($userId)) {
            abort(403);
        }
    }

    private function authorizeStudentOrTeacher(int $userId, WorkerContract $wc): void
    {
        if ($wc->groupMember?->user_id === $userId) {
            return; // student OK
        }
        // teacher?
        $clients = $wc->contract?->clients?->pluck('id') ?? collect();
        if ($clients->contains($userId)) {
            return; // teacher OK
        }
        abort(403);
    }

    /**
     * Vue imprimable pour export PDF (via impression navigateur).
     */
    public function export(Request $request, WorkerContract $workerContract)
    {
        $user = auth()->user();
        $this->authorizeAccess($user->id, $workerContract);

        $logs = $workerContract->dayLogs()->orderBy('date')->get();

        return view('journal.export', compact('workerContract', 'logs'));
    }
}
