<?php

namespace App\Http\Controllers;

use App\Models\AppreciationVersion;
use App\Models\Criterion;
use App\Models\Evaluation;
use App\Models\EvaluationVersion;
use App\Models\Remark;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EvalPulseController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'job_definition_id' => 'required|exists:job_definitions,id',
            'worker' => 'required|email|exists:users,email',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $student = User::where('email', $request->worker)->firstOrFail();

        $evaluation = Evaluation::create([
            'eleve_id' => $student->id,
            'teacher_id' => Auth::id(),
            'job_definition_id' => $request->job_definition_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'encours',
        ]);

        return redirect()->route('eval_pulse.show', $evaluation->id);
    }

    public function bulkEvaluate($ids)
    {
        $contractIds = explode(',', $ids);
        $evaluations = collect();

        foreach ($contractIds as $contractId) {
            // Find the contract and its related data
            // We need the student (worker) and the job definition
            // The contract ID passed here is likely the WorkerContract ID (pivot) or the Contract ID?
            // Looking at client-job-list.blade.php: value="{{$contract->id}}" where $contract is from contractsAsAClientForJob
            // contractsAsAClientForJob returns WorkerContract models (pivot)

            $workerContract = \App\Models\WorkerContract::with(['contract.jobDefinition', 'groupMember.user'])->find($contractId);

            if (!$workerContract) {
                continue;
            }

            $student = $workerContract->groupMember->user;
            $jobDefinition = $workerContract->contract->jobDefinition;
            $teacherId = Auth::id();

            // Find or create the evaluation
            $evaluation = Evaluation::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'teacher_id' => $teacherId,
                    'job_definition_id' => $jobDefinition->id,
                ],
                [
                    'status' => 'encours',
                    'start_date' => $workerContract->contract->start,
                    'end_date' => $workerContract->contract->end,
                ]
            );

            $evaluations->push($evaluation);
        }

        $evaluations = \Illuminate\Database\Eloquent\Collection::make($evaluations);
        $evaluations->load(['student', 'teacher', 'jobDefinition', 'versions.appreciations', 'versions.generalRemark', 'versions.creator']);
        $criteria = Criterion::orderBy('position')->get();
        $templates = \App\Models\RemarkTemplate::whereNull('user_id')->orWhere('user_id', Auth::id())->get();

        return view('eval_pulse.show', compact('evaluations', 'criteria', 'templates'));
    }

    public function update(Request $request, Evaluation $evaluation)
    {
        // Logic to add a new version
        $request->validate([
            'appreciations' => 'required|array',
            'appreciations.*.value' => 'required|in:NA,PA,A,LA',
            'appreciations.*.remark' => 'nullable|string',
            'appreciations.*.is_ignored' => 'nullable|boolean',
            'general_remark' => 'nullable|string',
            'status' => 'nullable|in:encours,clos',
        ]);

        DB::transaction(function () use ($request, $evaluation) {
            // Update evaluation status if provided
            if ($request->filled('status')) {
                $evaluation->update(['status' => $request->status]);
            }

            $generalRemarkId = null;
            if ($request->filled('general_remark')) {
                $remark = Remark::create([
                    'text' => $request->general_remark,
                    'author_user_id' => Auth::id(),
                ]);
                $generalRemarkId = $remark->id;
            }

            $versionNumber = $evaluation->versions()->max('version_number') + 1;

            $version = EvaluationVersion::create([
                'evaluation_id' => $evaluation->id,
                'version_number' => $versionNumber,
                'created_by_user_id' => Auth::id(),
                'general_remark_id' => $generalRemarkId,
            ]);

            foreach ($request->appreciations as $criterionId => $data) {
                $remarkId = null;
                if (!empty($data['remark'])) {
                    $remark = Remark::create([
                        'text' => $data['remark'],
                        'author_user_id' => Auth::id(),
                    ]);
                    $remarkId = $remark->id;
                }

                AppreciationVersion::create([
                    'version_id' => $version->id,
                    'criterion_id' => $criterionId,
                    'value' => $data['value'],
                    'remark_id' => $remarkId,
                    'is_ignored' => isset($data['is_ignored']) ? $data['is_ignored'] : false,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Evaluation saved successfully.');
    }
}
