<?php

namespace App\Http\Controllers;

use App\Constants\RemediationStatus;
use App\Constants\RoleName;
use App\DateFormat;
use App\Enums\RequiredTimeUnit;
use App\Http\Middleware\AcademicPeriodFilter;
use App\Http\Requests\ContractEvaluationRequest;
use App\Http\Requests\DestroyAllContractRequest;
use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractBulkRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Models\AcademicPeriod;
use App\Models\Contract;
use App\Models\JobDefinition;
use App\Models\JobDefinitionPart;
use App\Models\User;
use App\Models\WorkerContract;
use App\Models\WorkerContractEvaluationLog;
use App\SwissFrenchDateFormat;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Ramsey\Uuid\Guid\Guid;

class ContractController extends Controller
{
    public function __construct()
    {
        //map rbac authorization from policyClass
        $this->authorizeResource(Contract::class, 'contract');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
        abort(404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|Response
     */
    public function create(JobDefinition $jobDefinition)
    {
        //Not used...see createApply...
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse|Response
     */
    public function store(StoreContractRequest $request)
    {
        //SECURITY CHECKS (as this area is opened to students who might want to play with ids...)

        $jobDefinitionId = $request->get('job_definition_id');

        $jobDefinition = JobDefinition::whereId($jobDefinitionId)->firstOrFail();
        if (! $jobDefinition->isPublished()) {
            return back()->withErrors(__('Cannot apply for a draft/upcoming job...'))->withInput();
        }

        //Default part (1 eval per project) name is empty string
        $parts = JobDefinitionPart::query()->where('job_definition_id', '=', $jobDefinitionId)->get();
        $partsDetails = collect();
        if ($parts->isEmpty()) {
            $partsDetails->add([
                'name' => '',
                'clientId' => $request->input('client-0'),
                'time' => $jobDefinition->allocated_time,
                //todo timeunit
            ]);
        } else {
            $parts->each(function (JobDefinitionPart $part) use ($partsDetails, $request) {

                //Allow a teacher manually add a job without showing parts options on the UI
                //=>when doing this, the teacher will be the client for all parts (student can change that afterwards if needed...)
                $clientIdKey = 'client-' . $part->id;
                if ($request->has($clientIdKey)) {
                    $clientId = $request->input($clientIdKey);
                } else {
                    $clientId = $request->input('client-0');
                }
                $partsDetails->add([
                    'name' => $part->name,
                    'clientId' => $clientId,
                    'time' => $part->allocated_time,
                    //todo timueunit
                ]);
            });
        }

        foreach ($partsDetails as $partsDetail) {
            //Only teachers and authorized providers can be client
            $client = User::whereId($partsDetail['clientId']);
            if (! $client->exists() || ! $client->firstOrFail()->hasRole(RoleName::TEACHER) /* any teacher can be a client... ||
            !JobDefinition::whereHas('providers', function (Builder $query) use($jobDefinitionId,$client) {
                $query->where('user_id','=',$client->id)->where('job_definition_id','=',$jobDefinitionId);
            })->exists()*/) {
                return back()->withErrors(__('Invalid client (only valid providers are allowed)'))->withInput();
            }
        }

        /* @var $loggedUser User */
        /* @var $targetWorker User */
        $loggedUser = auth()->user();
        if ($request->has('worker')) {
            //Teachers can manually add a contract
            if ($loggedUser->hasRole(RoleName::TEACHER)) {
                $targetWorker = User::where('email', '=', $request->input('worker'))->firstOrFail();

                //As teacher adds a contract via modal, full error must be printed in root toast
                Session::flash('printErrors');
            } else {
                return back()->withErrors(__('Only teachers can assign custom worker'))->withInput();
            }
        } //If not teacher with worker, only students can apply for contract
        else {
            if (! $loggedUser->hasRole(RoleName::STUDENT) /*double check role... but should be done with permissions*/) {
                return back()->withErrors(__('Invalid worker (only students are allowed)'))->withInput();
            }
            //we expect mainly that students will apply... but in special cases a teacher can make an assignment... (student sick...)
            $targetWorker = $loggedUser;
        }
        //END OF SECURITY CHECKS/double checks

        //check that this user has not yet a contract for this job def
        if ($targetWorker->contractsAsAWorker()
            ->where('job_definition_id', '=', $jobDefinitionId)
            ->whereIn('name', $partsDetails->pluck('name'))
            ->exists()
        ) {
            return back()->withErrors(__('There already is a contract for this job'))->withInput();
        }

        //This shoud be checked in any date update
        $period = AcademicPeriod::current(false);
        $start = Carbon::createFromFormat(DateFormat::HTML_FORMAT, $request->input('start_date'));
        $end = Carbon::createFromFormat(DateFormat::HTML_FORMAT, $request->input('end_date'));
        if ($start->isBefore($period->start) || $end->isAfter($period->end)) {
            return back()->withErrors(__('Dates must be within current academic period'))->withInput();
        }
        $wishPriority = $request->has('wish_priority') ? $request->input('wish_priority') : 0;

        $firstContract = null;
        DB::transaction(function () use ($start, $end, &$firstContract, $jobDefinitionId, $partsDetails, $targetWorker, $wishPriority) {
            foreach ($partsDetails as $partsDetail) {
                $contract = Contract::make();
                $contract->start = $start;
                $contract->end = $end;

                $contract->jobDefinition()->associate($jobDefinitionId);

                //Consistency on error
                $clientId = $partsDetail['clientId'];

                $contract->save();
                $contract->clients()->attach($clientId);
                Cache::forget('client-' . $clientId . '-percentage');
                Cache::forget("involvedGroupNames-$clientId");
                $contract->workers()->attach($targetWorker->groupMember()->id); //set worker

                /* @var $workerContract WorkerContract */
                $workerContract = $contract->workerContract($targetWorker->groupMember())->firstOrFail();
                $workerContract->name = $partsDetail['name'];
                $workerContract->allocated_time = $partsDetail['time'];
                $workerContract->application_status = $wishPriority;
                $workerContract->save();

                if ($firstContract == null) {
                    $firstContract = $contract;
                }
            }
        });

        return redirect('/dashboard')
            ->with('success', __('New contract successfully registered'))
            ->with('contractId', $firstContract->id);
    }

    public function createApply(JobDefinition $jobDefinition)
    {
        $parts = JobDefinitionPart::query()->where('job_definition_id', '=', $jobDefinition->id)->get();
        //add dummy default if needed
        if ($parts->isEmpty()) {
            $mainJob = JobDefinitionPart::make();
            $mainJob->id = 0;
            $parts = collect()->add($mainJob);
        }

        //form to apply for a job
        return view('job-apply')->with(compact('jobDefinition', 'parts'));
    }

    /**
     * Show the Applicant / Job matrix
     */
    public function pendingContractApplications()
    {
        // row header
        $applicants = User::query()
            ->whereHas('groupMembers.workerContracts', function ($query) {
                $query->where('application_status', '>', 0);
            })
            ->get()
            ->transform(function (User $user) {
                return $user->firstname .' '. $user->lastname;
            })
            ->toArray();
        // column headers
        $jobTitles = JobDefinition::query()
            ->whereHas('contracts.workersContracts', function ($query) {
                $query->where('application_status', '>', 0);
            })
            ->select('title')
            ->pluck('title')
            ->toArray();

        // table content, indexed by applicant names and job titles
        $matrix = array(array());
        foreach (WorkerContract::where('application_status', '>', 0)->get() as $app) {
            $matrix[$app->groupMember->user->firstname . " " . $app->groupMember->user->lastname][$app->contract->jobDefinition->title] = $app;
        }
        return view('pendingApplications-view')->with(compact('matrix', 'applicants', 'jobTitles'));
    }

    /**
     * Confirms a worker application for a job for which he had expressed a wish
     * This will make the mandate permanent and remove all other demands for the job
     * unless explicitely told not to
     */
    public function confirmApplication(Request $request)
    {
        //SECURITY CHECKS (as this area is opened to students who might want to play with ids...)
        $this->authorize('contracts.edit');

        $wc = WorkerContract::find($request->input('applicationid'));
        $wc->application_status = 0;
        $wc->save();

        // Destroy other contracts, unless explicitely told otherwise
        if (!$request->has('keep')) {
            // Gather the affected contracts
            $contracts = Contract::where('job_definition_id', $wc->contract->job_definition_id)
                ->where('id', '!=', $wc->contract_id)
                ->get()
                ->pluck('id')
                ->toArray();
            // Must delete logs explicitely, because soft deletes won't cascade
            WorkerContractEvaluationLog::whereIn('contract_id', $contracts)->forceDelete();
            Contract::whereIn('id', $contracts)->forceDelete();
        }
        return redirect('/applications');
    }

    /**
     * Cancel a pending application (i.e: application_status > 0)
     * Only the subscriber is allowed to do it
     */
    public function cancelApplication(Request $request)
    {
        $app = WorkerContract::find($request->input('applicationid'));
        if (!$app || $app->groupMember->user_id != Auth::user()->id || $app->application_status <= 0) {
            return redirect('/dashboard')
                ->with('error', __('Something wrong happened...'));
        }
        $app->delete();
        return redirect('/dashboard')
            ->with('success', __('Your resignation has been noted'));
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Contract $contract)
    {
        //
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Contract $contract)
    {
        //
        abort(404);
    }

    /**
     * @throws \Throwable
     */
    public function bulkUpdate(UpdateContractBulkRequest $request)
    {
        //SECURITY CHECKS (as this area is opened to students who might want to play with ids...)
        $this->authorize('contracts.edit');

        $updated = 0;

        $contracts = $this->getContractsForModifications(collect($request->input('workersContracts'))->join(','), true);
        $starts = $request->input('starts');
        $ends = $request->input('ends');
        $allocated_times = $request->input('allocated_times');
        $workersContracts = $request->input('workersContracts');

        DB::beginTransaction();
        try {
            foreach ($contracts->all() as $i => $contract) {
                $updateRequest = UpdateContractRequest::createFrom($request);
                $updateRequest->replace([
                    'start' => DateFormat::DateFromHtmlInput($starts[$i]),
                    'end' => DateFormat::DateFromHtmlInput($ends[$i]),
                    'allocated_time' => $allocated_times[$i],
                ]);

                //TODO: is policy still applied here ?
                $workerContract = WorkerContract::whereId($workersContracts[$i])->firstOrFail();
                $result = $this->update($updateRequest, $contract, $i, $workerContract);
                //Validation error...
                if ($result instanceof \Symfony\Component\HttpFoundation\Response) {
                    DB::rollBack();

                    return $result;
                }
                $updated += $result;
            }

            //Only save if no errors...
            $contracts->each(fn($c) => $c->save());
            DB::commit();
        } catch (\Throwable $t) {
            DB::rollBack();
            throw $t;
        }

        return $this->createUpdateResponse($updated);
    }

    //TODO refactor to have only use workercontract... ?

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContractRequest $request, Contract $contract, $index = -1 /*used in case of bulk*/, ?WorkerContract $workerContract = null /*used in case of bulk*/): \Symfony\Component\HttpFoundation\Response|int
    {
        DB::beginTransaction();
        try {
            //TODO WARNING: policy must be manually applied if called from other method !!!
            $bulk = $index != -1;

            //Validate start/end date
            $period = AcademicPeriod::current(false);
            $user = auth()->user();
            $isUpdated = false;

            //Teachers reserved (except date that can be adjusted upon remediation too)
            if($user->hasRole(RoleName::TEACHER)) {

                $dateResult = $this->handleDateAdjustment($request, $contract,$period,$user,$bulk,$index);
                if($dateResult instanceof \Symfony\Component\HttpFoundation\RedirectResponse) {
                    return $dateResult;
                }
                $isUpdated |= $dateResult;

                //Allocated time only allowed through bulk currently
                if ($workerContract !== null) {
                    $allocated_time = $request->input('allocated_time');
                    if ($allocated_time !== null && $allocated_time != $workerContract->getAllocatedTime(RequiredTimeUnit::PERIOD)) {
                        $workerContract->allocated_time = $allocated_time;
                        $isUpdated |= $workerContract->save();
                        Log::info('userid ' . $user->id . ' updated worker contract with id ' . $workerContract->id
                            . ' => allocated_time : ' . $allocated_time);
                    }
                }

                $remediationAccept = $request->input('remediation-accept');
                if ($remediationAccept !== null) {
                    $workerContract = $contract->workersContracts()->firstOrFail();
                    /** @noinspection PhpIntRangesMismatchInspection */
                    $workerContract->remediation_status = $remediationAccept ?
                        RemediationStatus::CONFIRMED_BY_CLIENT
                        :RemediationStatus::REFUSED_BY_CLIENT;
                    $isUpdated |= $workerContract->save();
                }

                if ($isUpdated) {
                    Log::info('userid ' . $user->id . ' updated worker contract with id ' . $workerContract->id
                        . ' => remediation status : '.$workerContract->remediation_status);
                }

            }

            else if ($user->hasRole(RoleName::STUDENT))
            {
                //Update clients (only or with remediation)
                $clientId = $request->input('clientId');
                if ($clientId !== null) {
                    $periodIdFilter = $request->get(AcademicPeriodFilter::ACADEMIC_PERIOD_ID_REQUEST_PARAM);
                    $gm = $user->groupMember($periodIdFilter);
                    $workerContract = $contract->workerContract($gm)->firstOrFail();
                    if ($workerContract->alreadyEvaluated() && !$workerContract->canRemediate()) {
                        Log::warning('userid ' . $user->id . ' tried to update non remediable already evaluated contract with id ' . $contract->id . ' => clientId to ' . $clientId);
                    } else {
                        $oldClientId = $contract->clients->firstOrFail()->id;
                        $changes = $contract->clients()->sync([$clientId]);
                        if (collect($changes)->transform(fn($k) => count($k))->sum() > 0) {
                            $isUpdated |= true;
                            Cache::forget('client-' . $oldClientId . '-percentage');
                            Cache::forget('client-' . $clientId . '-percentage');
                            Cache::forget("involvedGroupNames-$clientId");
                            Log::info('userid ' . $user->id . ' updated contract with id ' . $contract->id . ' : client moved from id ' . $oldClientId . ' => ' . $clientId);
                        }

                        //Remediation
                        if($workerContract->canRemediate())
                        {
                            $start = Carbon::createFromFormat(DateFormat::HTML_FORMAT, $request->input('start_date'));
                            $end = Carbon::createFromFormat(DateFormat::HTML_FORMAT, $request->input('end_date'));
                            $request->merge(["start"=>$start,"end"=>$end]);
                            $dateResult = $this->handleDateAdjustment($request, $contract,$period,$user,$bulk,$index);
                            if($dateResult instanceof \Symfony\Component\HttpFoundation\RedirectResponse) {
                                return $dateResult;
                            }
                            $isUpdated |= $dateResult;

                            /** @noinspection PhpIntRangesMismatchInspection */
                            $workerContract->remediation_status = RemediationStatus::ASKED_BY_WORKER;
                            $isUpdated |= $workerContract->save();
                        }
                    }
                }
            }

            //Smartly end transaction /!\WARNING: either force commit (even without changes), OR let this code because if transaction is not terminated
            //it will make a mess...
            if ($isUpdated) {
                DB::commit();
            } else {
                DB::rollBack();
            }
            $updatedCount = $isUpdated ? 1 : 0;
            if ($bulk) {
                return $updatedCount;
            } else {
                return $this->createUpdateResponse($updatedCount);
            }
        } catch (\Throwable $t) {
            DB::rollBack();
            throw $t;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Contract $contract)
    {
        throw new \Exception('Not implemented :-(');
    }

    public function destroyAll(DestroyAllContractRequest $request)
    {
        //TODO UI should send worker_contract id , not contract id...

        $user = auth()->user();

        if (! $user->can('contracts.trash')) {
            Log::warning('missing role to delete contract');
            abort(403, 'You are not allowed to do this');
        }

        $jobId = $request->get('job_id');
        $contracts = $request->get('job-' . $jobId . '-contracts');

        $deleted = DB::transaction(function () use ($user, $contracts) {
            $deleted = 0;
            WorkerContract::whereIn('contract_id', $contracts)->with('contract.clients')->each(
                function (WorkerContract $workerContract) use (&$deleted, $user) {
                    if ($user->can('contracts') || $workerContract->contract->clients->find($user->id) !== null) {
                        //Manual trash as WorkContract is a pivot and cannot softdelete
                        if ($workerContract->update(['deleted_at' => now()])) {
                            $deleted++;
                            Log::info('userid' . $user->id . ' deleted worker contract with id ' . $workerContract->id);
                            $clientId = $workerContract->contract->clients->firstOrFail()->id;
                            Cache::forget('client-' . $clientId . '-percentage');
                            Cache::forget("involvedGroupNames-$clientId");

                            //softdelete contract if not any workers on it...
                            $contractDeleted = $workerContract->contract->whereDoesntHave(
                                'workersContracts',
                                function ($query) {
                                    return $query->whereNull('deleted_at');
                                }
                            )->delete();

                            Log::info('userid' . $user->id . ' also deleted ' . $contractDeleted . ' related contract with id ' . $workerContract->contract->id);
                        }
                    } else {
                        Log::warning('trying to delete contracts which do not belong');
                    }
                }
            );

            return $deleted;
        });

        if ($deleted > 0) {
            return redirect('/dashboard')
                ->with(
                    'success',
                    trans_choice(':number contract deleted|:number contracts deleted', $deleted, ['number' => $deleted])
                );
        } else {
            return redirect('/dashboard')
                ->with('error', __('No contract deleted, wrong request ?'));
        }
    }

    public function evaluate(string $ids)
    {
        $this->authorize('contracts.evaluate');

        return $this->getBulkView($ids, view('contracts-evaluate'));
    }

    public function bulkEdit(string $ids)
    {
        $this->authorize('contracts.edit');

        return $this->getBulkView($ids, view('contracts-bulkEdit'));
    }

    public function evaluateApply(ContractEvaluationRequest $request)
    {

        $contracts = $this->getContractsForModifications(collect($request->workersContracts)->join(','), true);

        // Handle attachments marked for deletion
        if ($request->has('attachmentsToDelete') && !empty($request->input('attachmentsToDelete'))) {
            $attachmentIds = json_decode($request->input('attachmentsToDelete'), true);
            if (is_array($attachmentIds)) {
                $this->deleteEvaluationAttachments($attachmentIds);
            }
        }

        $updated = 0;
        foreach ($contracts as $contract) {
            foreach ($contract->workersContracts as $workerContract) {

                $evaluationResult = $request->input('evaluation_result-' . $workerContract->id);

                // Validate evaluation result - abort if invalid
                if (!in_array($evaluationResult, ['na', 'pa', 'a', 'la'])) {
                    return back()
                        ->withErrors(['evaluation_result-' . $workerContract->id => __('Invalid evaluation result')])
                        ->withInput();
                }

                $comment = null;
                // Require comment for failed or partially acquired evaluations
                if (in_array($evaluationResult, ['na', 'pa'])) {
                    $commentAttributeName = 'comment-' . $workerContract->id;
                    $comment = $request->input($commentAttributeName);
                    if (empty(trim($comment))) {
                        return back()
                            ->withErrors([$commentAttributeName => __('Failed jobs must have a clue for improvement')])
                            ->withInput();
                    }
                }

                if ($workerContract->evaluate($evaluationResult, $comment)) {
                    $updated++;
                }
            }

            // In case of error, only do that if all went well !
            // Finalize evaluation attachments: move from temporary to final storage
            foreach ($contract->workersContracts as $workerContract) {
                $this->finalizeEvaluationAttachments($workerContract);
            }
        }

        return redirect('/dashboard')
            ->with(
                'success',
                trans_choice(':number contract updated|:number contracts updated', $updated, ['number' => $updated])
            );
    }

    /**
     * @return Contract[]|Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    protected function getContractsForModifications(string $ids, bool $workersContractsIds = false): \Illuminate\Support\Collection|array|\Illuminate\Database\Eloquent\Collection
    {
        $queryIds = collect(explode(',', $ids))->filter(fn($el) => is_numeric($el))->toArray();

        $query = Contract::query();

        if ($workersContractsIds) {
            $query->whereHas('workersContracts', fn($q) => $q->whereIn(tbl(WorkerContract::class) . '.id', $queryIds));
        } else {
            $query->whereIn('id', $queryIds);
        }

        //Non admin users can only modify their contracts...
        $user = auth()->user();
        if ($user->cannot('contracts')) {
            $query->whereHas('clients', fn($q) => $q->where('user_id', '=', $user->id));
        }

        return $query
            ->with('workers.user')
            ->with('workersContracts.groupMember')
            ->with('workersContracts.evaluationAttachments')
            ->get();
    }

    public function createUpdateResponse(int $updated): \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    {
        $message = ['warning', __('No changes detected')];
        if ($updated > 0) {
            $message = ['success', trans_choice(':number contract updated|:number contracts updated', $updated, ['number' => $updated])];
        }

        return redirect('/dashboard')
            ->with($message[0], $message[1]);
    }

    public function getBulkView(string $ids, $view): \Illuminate\Contracts\Foundation\Application|Factory|View
    {
        $contracts = $this->getContractsForModifications($ids);
        $job = $contracts->firstOrFail()->jobDefinition;

        return $view->with(compact('contracts', 'job'));
    }

    private function handleDateAdjustment(UpdateContractRequest $request, Contract $contract,AcademicPeriod $period,
                                          User $user,bool $bulk,int $index) : RedirectResponse|bool
    {
        /* @var $start Carbon */
        $start = $request->input('start');
        $end = $request->input('end');
        if ($start != null && $end != null) {
            if ($start->isBefore($period->start) || $end->isAfter($period->end)) {
                $message = __('Dates must be included within current academic period');
                $errors = ['workersContract' . ($bulk ? "s.$index" : '') => $message];

                return back()->withErrors($errors)->withInput();
            } elseif ($start->isAfter($end)) {
                $message = __(
                    'Start date :start must be before end date :end',
                    [
                        'start' => $start->format(SwissFrenchDateFormat::DATE),
                        'end' => $end->format(SwissFrenchDateFormat::DATE)
                    ]
                );
                $errors = ['workersContract' . ($bulk ? "s.$index" : '') => $message];

                return back()->withErrors($errors)->withInput();
            }

            //Smart update
            foreach (['start', 'end'] as $field) {
                /* @var $newDate \Carbon\Carbon */
                $newDate = $request->input($field);
                if (!$newDate->isSameDay($contract->$field)) {
                    $contract->$field = $newDate;
                    $isUpdated = $contract->save();
                    if ($isUpdated) {
                        Log::info('userid ' . $user->id . ' updated contract with id ' . $contract->id . ' => ' . $field . ' to ' . $newDate);
                    }
                }
            }
            return $isUpdated??false;
        }
        return false;
    }

    private function deleteEvaluationAttachments(array $attachmentIds): void
    {
        // Single query with IN operator, then delete each to trigger model events
        \App\Models\ContractEvaluationAttachment::whereIn('id', $attachmentIds)
            ->each(function ($attachment) {
                $attachment->delete();
            });
    }

    private function finalizeEvaluationAttachments(WorkerContract $workerContract): void
    {
        foreach ($workerContract->evaluationAttachments()->get() /*Force refresh*/ as $attachment) {
            // Only process if attachment is in temporary storage
            if (str_contains($attachment->storage_path, 'pending')) {
                // Get file extension from original path
                $pathInfo = pathinfo($attachment->storage_path);
                $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';

                // Generate unique filename with contract-doc- prefix
                do {
                    $uuid = Guid::uuid4()->toString();
                    $finalPath = "eval-wcid" . $workerContract->id . '-' . $uuid . "." . $extension;
                } while (uploadDisk()->exists($finalPath));

                // Get encrypted content from temporary location
                $encryptedContent = uploadDisk()->get($attachment->storage_path);

                // Vérifier que le contenu existe
                if ($encryptedContent === null) {
                    \Log::debug('File not found or unreadable, it has probably already been moved', [
                        'storage_path' => $attachment->storage_path,
                        'attachment_id' => $attachment->id
                    ]);
                }
                // Store in final location
                else if (uploadDisk()->put($finalPath, $encryptedContent)) {
                    // Delete temporary file last (after updating path)
                    $tempPath = $attachment->storage_path;

                    // Update attachment storage path
                    $attachment->storage_path = $finalPath;
                    $attachment->save();

                    // Delete temporary file
                    uploadDisk()->delete($tempPath);

                    \Log::debug('File moved', [
                        'source' => $tempPath,
                        'destination' => $finalPath
                    ]);
                }
            }
        }
    }
}
