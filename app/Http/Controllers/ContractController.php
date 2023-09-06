<?php

namespace App\Http\Controllers;

use App\Constants\RoleName;
use App\DateFormat;
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
use App\SwissFrenchDateFormat;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContractController extends Controller
{
    public function __construct()
    {
        //map rbac authorization from policyClass
        $this->authorizeResource(Contract::class,'contract');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|Response
     */
    public function create(JobDefinition $jobDefinition)
    {
        //Not used...see createApply...
        return view('contract-create')->with(compact('jobDefinition'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreContractRequest  $request
     * @return \Illuminate\Http\RedirectResponse|Response
     */
    public function store(StoreContractRequest $request)
    {
        //SECURITY CHECKS (as this area is opened to students who might want to play with ids...)
        $this->authorize('jobs-apply');

        $jobDefinitionId = $request->get('job_definition_id');

        $jobDefinition = JobDefinition::whereId($jobDefinitionId)->firstOrFail();
        if(! $jobDefinition->isPublished())
        {
            return back()->withErrors(__('Cannot apply for a draft/upcoming job...)'))->withInput();
        }

        //Default part (1 eval per project) name is empty string
        $parts = JobDefinitionPart::query()->where('job_definition_id','=',$jobDefinitionId)->get();
        $partsDetails=collect();
        if($parts->isEmpty()){
            $partsDetails->add([
                'name'=>"",
                'clientId'=>$request->input('client-0'),
                'time'=>$jobDefinition->allocated_time,
                //todo timeunit
                ]);
        }else{
            $parts->each(function(JobDefinitionPart $part) use ($partsDetails,$request,$jobDefinition){
                $partsDetails->add([
                    'name'=>$part->name,
                    'clientId'=>$request->input("client-".$part->id),
                    'time'=>$part->allocated_time,
                    //todo timueunit
                ]);
            });
        }

        foreach ($partsDetails as $partsDetail){
            //Only teachers and authorized providers can be client
            $client = User::whereId($partsDetail['clientId']);
            if(!$client->exists() || !$client->firstOrFail()->hasRole(RoleName::TEACHER) /* any teacher can be a client... ||
            !JobDefinition::whereHas('providers', function (Builder $query) use($jobDefinitionId,$client) {
                $query->where('user_id','=',$client->id)->where('job_definition_id','=',$jobDefinitionId);
            })->exists()*/)
            {
                return back()->withErrors(__('Invalid client (only valid providers are allowed)'))->withInput();
            }
        }


        //Only students can be workers
        /* @var $user User */
        $user = auth()->user();
        if(!$user->hasRole(RoleName::STUDENT))
        {
            return back()->withErrors(__('Invalid worker (only students are allowed)'))->withInput();
        }
        //END OF SECURITY CHECKS


        //check that this user has not yet a contract for this job def
        if ($user->contractsAsAWorker()
            ->where('job_definition_id','=',$jobDefinitionId)
            ->whereIn('name',$partsDetails->pluck('name'))
            ->exists())
        {
            return back()->withErrors(__('You already have/had a contract for this job'))->withInput();
        }

        //This shoud be checked in any date update
        $period = AcademicPeriod::current(false);
        $start = Carbon::createFromFormat(DateFormat::HTML_FORMAT,$request->input('start_date'));
        $end =  Carbon::createFromFormat(DateFormat::HTML_FORMAT, $request->input('end_date'));
        if($start->isBefore($period->start) || $end->isAfter($period->end))
        {
            return back()->withErrors(__('Dates must be within current academic period'))->withInput();
        }

        $firstContract=null;
        DB::transaction(function () use ($start,$end,&$firstContract,$jobDefinitionId,$partsDetails,$request,$user) {
            foreach($partsDetails as $partsDetail){
                $contract = Contract::make();
                $contract->start = $start;
                $contract->end = $end;

                $contract->jobDefinition()->associate($jobDefinitionId);

                //Consistency on error
                $clientId = $partsDetail['clientId'];

                $contract->save();
                $contract->clients()->attach($clientId);
                $contract->workers()->attach($user->groupMember()->id);//set worker

                /* @var $workerContract WorkerContract */
                $workerContract = $contract->workerContract($user->groupMember())->firstOrFail();
                $workerContract->name=$partsDetail['name'];
                $workerContract->allocated_time=$partsDetail['time'];
                $workerContract->save();

                if($firstContract==null){
                    $firstContract=$contract;
                }
            }
        });

        return redirect('/dashboard')
            ->with('success',__('Congrats, you have been hired for the job'))
            ->with('contractId',$firstContract->id);
    }

    public function createApply(JobDefinition $jobDefinition)
    {
        $parts = JobDefinitionPart::query()->where('job_definition_id','=',$jobDefinition->id)->get();
        //add dummy default if needed
        if($parts->isEmpty()){
            $mainJob = JobDefinitionPart::make();
            $mainJob->id=0;
            $parts=collect()->add($mainJob);
        }

        //form to apply for a job
        return view('job-apply')->with(compact('jobDefinition','parts'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Contract  $contract
     * @return Response
     */
    public function show(Contract $contract)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Contract  $contract
     * @return Response
     */
    public function edit(Contract $contract)
    {
        //
    }

    public function bulkUpdate(UpdateContractBulkRequest $request)
    {
        //SECURITY CHECKS (as this area is opened to students who might want to play with ids...)
        $this->authorize('contracts.edit');

        $contracts = $this->getContractsForModifications(collect($request->get("workersContracts"))->join(','),true);
        $starts = $request->get("starts");
        $ends = $request->get("ends");
        $updated = 0;
        foreach($contracts->all() as $i=>$contract)
        {
            $updateRequest= UpdateContractRequest::createFrom($request);
            $updateRequest->replace(["start"=>DateFormat::DateFromHtmlInput($starts[$i]),"end"=>DateFormat::DateFromHtmlInput($ends[$i])]);

            //TODO: is policy still applied here ?
            $result = $this->update($updateRequest,$contract,$i);
            //Validation error...
            if($result instanceof \Symfony\Component\HttpFoundation\Response)
            {
                return $result;
            }
            $updated += $result;
        }

        //Only save if no errors...
        $contracts->each(fn($c)=>$c->save());

        return $this->createUpdateResponse($updated);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateContractRequest  $request
     * @param  \App\Models\Contract  $contract
     * @return \Symfony\Component\HttpFoundation\Response | int
     */
    public function update(UpdateContractRequest $request, Contract $contract, $index=-1) : \Symfony\Component\HttpFoundation\Response | int
    {
        //TODO: if policy is not applied on $this->update call (like spring proxy in java), policy must be manually applied...
        $bulk=$index!=-1;

        //Validate start/end date
        $period = AcademicPeriod::current(false);
        /* @var $start Carbon */
        $start=$request->input('start');
        $end=$request->input('end');
        if($start->isBefore($period->start) || $end->isAfter($period->end))
        {
            $message =__('Dates must be included within current academic period');
            $errors = ["workersContract".($bulk?"s.$index":'')=>$message];
            return back()->withErrors($errors)->withInput();
        }
        else if($start->isAfter($end))
        {
            $message =__("Start date ".$start->format(SwissFrenchDateFormat::DATE)." must be before end date ".$end->format(SwissFrenchDateFormat::DATE));
            $errors = ["workersContract".($bulk?"s.$index":'')=>$message];
            return back()->withErrors($errors)->withInput();
        }

        //Smart update
        $isUpdated = false;
        $user = auth()->user();
        foreach (['start','end'] as $field)
        {
            /* @var $newDate \Carbon\Carbon */
            $newDate = $request->input($field);
            if(!$newDate->isSameDay($contract->$field))
            {
                $contract->$field=$newDate;
                Log::info("userid ".$user->id." updated contract with id ".$contract->id." => ".$field." to ".$newDate);
                $isUpdated=true;
            }
        }

        $updatedCount = $isUpdated?1:0;
        if($bulk)
        {
            return $updatedCount;
        }
        else
        {
            $contract->save();
            return $this->createUpdateResponse($updatedCount);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Contract  $contract
     * @return Response
     */
    public function destroy(Contract $contract)
    {
        //
    }

    public function destroyAll(DestroyAllContractRequest $request)
    {
        //TODO UI should send worker_contract id , not contract id...

        $user=auth()->user();

        if(!$user->can('contracts.trash')){
            Log::warning("missing role to delete contract");
            abort(403,'You are not allowed to do this');
        }

        $jobId = $request->get('job_id');
        $contracts = $request->get('job-'.$jobId.'-contracts');

        $deleted = DB::transaction(function () use ($user,$contracts,$jobId) {
            $deleted=0;
            WorkerContract::whereIn('contract_id', $contracts)->with('contract.clients')->each(
                function(WorkerContract $workerContract) use(&$deleted,$user)
                {
                    if($user->can('contracts') || $workerContract->contract->clients->find($user->id)!==null){
                        //Manual trash as WorkContract is a pivot and cannot softdelete
                        if($workerContract->update(['deleted_at' => now()]))
                        {
                            $deleted++;
                            Log::info("userid".$user->id." deleted worker contract with id ".$workerContract->id);

                            //softdelete contract if not any workers on it...
                            $contractDeleted=$workerContract->contract->whereDoesntHave('workersContracts',
                                function($query){
                                        return $query->whereNull('deleted_at');
                                })->delete();

                            Log::info("userid".$user->id." also deleted ".$contractDeleted." related contract with id ".$workerContract->contract->id);
                        }
                    }
                    else{
                        Log::warning("trying to delete contracts which do not belong");
                    }

                }
            );

            return $deleted;
        });

        if($deleted>0)
        {
            return redirect('/dashboard')
                ->with('success',
                    trans_choice(':number contract deleted|:number contracts deleted',$deleted,['number'=>$deleted]));
        }
        else{
            return redirect('/dashboard')
                ->with('error', __('No contract deleted, wrong request ?'));
        }

    }

    public function evaluate(string $ids)
    {
        $this->authorize('contracts.evaluate');

        return $this->getBulkView($ids,view('contracts-evaluate'));
    }

    public function bulkEdit(string $ids)
    {
        $this->authorize('contracts.edit');
        return $this->getBulkView($ids,view('contracts-bulkEdit'));

    }

    public function evaluateApply(ContractEvaluationRequest $request)
    {

        $contracts = $this->getContractsForModifications(collect($request->workersContracts)->join(','),true);

        $updated=0;
        foreach($contracts as $contract)
        {
            foreach ($contract->workersContracts as $workerContract)
            {

                $success = filter_var($request->input('success-'.$workerContract->id),FILTER_VALIDATE_BOOLEAN);
                $comment = null;
                if(!$success)
                {
                    $commentAttributeName = 'comment-'.$workerContract->id;
                    $comment = $request->input($commentAttributeName);
                    if(empty(trim($comment)))
                    {
                        return back()
                            ->withErrors([$commentAttributeName => __('Failed jobs must have a clue for improvement')])
                            ->withInput();
                    }
                }
                if($workerContract->evaluate($success,$comment))
                {
                    $updated++;
                }

            }
        }


        return redirect('/dashboard')
            ->with('success',
                trans_choice(':number contract updated|:number contracts updated',$updated,['number'=>$updated]));
    }

    /**
     * @param string $ids
     * @return Contract[]|Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    protected function getContractsForModifications(string $ids, bool $workersContractsIds=false): \Illuminate\Support\Collection|array|\Illuminate\Database\Eloquent\Collection
    {
        $queryIds = collect(explode(',', $ids))->filter(fn($el) => is_numeric($el))->toArray();

        $query = Contract::query();

        if($workersContractsIds)
        {
            $query->whereHas('workersContracts',fn($q)=>$q->whereIn(tbl(WorkerContract::class).'.id',$queryIds));
        }
        else
        {
            $query->whereIn('id',$queryIds);
        }

        //Non admin users can only modify their contracts...
        $user=auth()->user();
        if($user->cannot('contracts'))
        {
            $query->whereHas('clients', fn($q) => $q->where('user_id', '=', $user->id));
        }

        return $query
            ->with('workers.user')
            ->with('workersContracts.groupMember')
            ->get();

    }

    /**
     * @param int $updated
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function createUpdateResponse(int $updated): \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    {
        $message = ['warning', __('No changes detected')];
        if ($updated > 0) {
            $message = ['success', trans_choice(':number contract updated|:number contracts updated', $updated, ['number' => $updated])];
        }

        return redirect('/dashboard')
            ->with($message[0], $message[1]);
    }

    /**
     * @param string $ids
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View
     */
    public function getBulkView(string $ids,$view): \Illuminate\Contracts\Foundation\Application|Factory|View
    {
        $contracts = $this->getContractsForModifications($ids);
        $job = $contracts->firstOrFail()->jobDefinition;

        return $view->with(compact('contracts', 'job'));
    }

}
