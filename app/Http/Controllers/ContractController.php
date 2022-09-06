<?php

namespace App\Http\Controllers;

use App\Constants\RoleName;
use App\Http\Requests\ContractEvaluationRequest;
use App\Http\Requests\DestroyAllContractRequest;
use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Models\Contract;
use App\Models\JobDefinition;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(JobDefinition $jobDefinition)
    {
        return view('contract-create')->with(compact('jobDefinition'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreContractRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreContractRequest $request)
    {
        //SECURITY CHECKS (as this area is opened to students who might want to play with ids...)
        $this->authorize('jobs-apply');

        $jobDefinitionId = $request->get('job_definition_id');

        /* @var $jobDefinition JobDefinition */
        $jobDefinition = JobDefinition::whereId($jobDefinitionId)->firstOrFail();
        if(! $jobDefinition->isPublished())
        {
            return back()->withErrors(__('Cannot apply for a draft/upcoming job...)'))->withInput();
        }

        //Only teachers and authorized providers can be client
        $client = User::whereId($request->get('client'))->firstOrFail();
        if(!$client->hasRole(RoleName::TEACHER) /* any teacher can be a client... ||
            !JobDefinition::whereHas('providers', function (Builder $query) use($jobDefinitionId,$client) {
                $query->where('user_id','=',$client->id)->where('job_definition_id','=',$jobDefinitionId);
            })->exists()*/)
        {
            return back()->withErrors(__('Invalid client (only valid providers are allowed)'))->withInput();
        }
        //Only students can be workers
        $user = auth()->user();
        if(!$user->hasRole(RoleName::STUDENT))
        {
            return back()->withErrors(__('Invalid worker (only students are allowed)'))->withInput();
        }
        //END OF SECURITY CHECKS


        //check that this user has not yet a contract for this job def
        if ($user->contractsAsAWorker()->where('job_definition_id','=',$jobDefinitionId)->exists())
        {
            return back()->withErrors(__('You already have/had a contract for this job'))->withInput();
        }

        $contract = Contract::make();
        $contract->start = $request->get('start_date');
        $contract->end = $request->get('end_date');
        $contract->jobDefinition()->associate($jobDefinitionId);

        //Consistency on error
        DB::transaction(function () use ($contract,$request,$client,$user) {
            $contract->save();
            $contract->clients()->attach($client->id);
            $contract->workers()->attach($user->groupMember()->id);//set worker
        });

        return redirect('/dashboard')
            ->with('success',__('Congrats, you have been hired for the job'))
            ->with('contractId',$contract->id);
    }

    public function createApply(JobDefinition $jobDefinition)
    {
        //form to apply for a job
        return view('job-apply')->with(compact('jobDefinition'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function show(Contract $contract)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function edit(Contract $contract)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateContractRequest  $request
     * @param  \App\Models\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateContractRequest $request, Contract $contract)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contract $contract)
    {
        //
    }

    public function destroyAll(DestroyAllContractRequest $request)
    {
        $user=auth()->user();//Policy ensure required role
        $jobId = $request->get('job_id');
        $contracts = $request->get('job-'.$jobId.'-contracts');

        $deleted = Contract::
            whereHas('jobDefinition',fn($q)=>$q->where(tbl(JobDefinition::class).'.id','=',$jobId))
            ->whereHas('clients',fn($q)=>$q->where(tbl(User::class).'.id','=',$user->id))
            ->whereIn(tbl(Contract::class).'.id',$contracts)
            ->delete();

        return redirect('/dashboard')
            ->with('success',
                trans_choice(':number contract deleted|:number contracts deleted',$deleted,['number'=>$deleted]));
    }

    public function evaluate(string $ids)
    {
        $this->authorize('contracts.evaluate');

        $contracts = $this->getContractsForEvaluation($ids);

        return view('contracts-evaluate')->with(compact('contracts'));
    }

    public function evaluateApply(ContractEvaluationRequest $request)
    {

        $contracts = $this->getContractsForEvaluation(collect($request->contracts)->join(','));

        $updated=0;
        foreach ($contracts as $contract)
        {

            $success = filter_var($request->input('success-'.$contract->id),FILTER_VALIDATE_BOOLEAN);
            $comment = null;
            if(!$success)
            {
                $commentAttributeName = 'comment-'.$contract->id;
                $comment = $request->input($commentAttributeName);
                if(empty(trim($comment)))
                {
                    return back()
                        ->withErrors([$commentAttributeName => __('Failed jobs must have a clue for improvement')])
                        ->withInput();
                }
            }
            if($contract->evaluate($success,$comment))
            {
                $updated++;
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
    protected function getContractsForEvaluation(string $ids): \Illuminate\Support\Collection|array|\Illuminate\Database\Eloquent\Collection
    {
        return Contract::query()
            ->whereIn('id', collect(explode(',', $ids))->filter(fn($el) => is_numeric($el))->toArray())
            ->whereHas('clients', fn($q) => $q->where('user_id', '=', auth()->user()->id))
            ->with('workers.user')
            ->get();

    }
}
