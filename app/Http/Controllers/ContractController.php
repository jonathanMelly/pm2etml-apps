<?php

namespace App\Http\Controllers;

use App\Enums\RoleName;
use App\Models\Contract;
use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
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
    }

    public function createApply(JobDefinition $jobDefinition)
    {
        //form to apply for a job
        return view('job-apply')->with(compact('jobDefinition'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreContractRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function storeApply(StoreContractRequest $request)
    {
        //SECURITY CHECKS (as this area is opened to students who might want to play with ids...)
        $this->authorize('jobs-apply');

        $jobDefinitionId = $request->get('job_definition_id');

        //Only prof and authorized providers can be client
        $client = User::whereId($request->get('client'))->firstOrFail();
        if(!$client->hasRole(RoleName::TEACHER) ||
            !JobDefinition::whereHas('providers', function (Builder $query) use($jobDefinitionId,$client) {
                $query->where('user_id','=',$client->id)->where('job_definition_id','=',$jobDefinitionId);
            })->exists())
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
        $contract->start_date = $request->get('start_date');
        $contract->end_date = $request->get('end_date');
        $contract->jobDefinition()->associate($jobDefinitionId);

        //Consistency on error
        DB::transaction(function () use ($contract,$request,$client,$user) {
            $contract->save();
            $contract->clients()->attach($client->id);
            $contract->workers()->attach($user->id);//set worker
        });

        return redirect('/dashboard')
            ->with('success',__('Congrats, you have been hired for the job'))
            ->with('contractId',$contract->id);
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
}
