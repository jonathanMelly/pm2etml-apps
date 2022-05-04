<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\JobDefinition;
use App\Http\Requests\StoreJobDefinitionRequest;
use App\Http\Requests\UpdateJobDefinitionRequest;


class JobDefinitionController extends Controller
{

    public function __construct()
    {
        //map rbac authorization from policyClass
        $this->authorizeResource(JobDefinition::class,'jobDefinition');
    }

    public function marketPlace()
    {
        $definitions = JobDefinition::query()
            ->where(fn($q)=>$q->published())
            ->where(fn($q)=>$q->available())
            ->whereNotIn('id',auth()->user()->contractsAsAWorker()->select('job_definition_id'))
            ->orderBy('required_xp_years')
            ->orderByDesc('one_shot')
            ->orderBy('priority')
            ->with('providers')
            ->get();
        return view('marketplace')->with(compact('definitions'));
    }

    /**
     * aka MarketPlace
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $definitions = JobDefinition::all();
        return $definitions;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreJobDefinitionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreJobDefinitionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\JobDefinition  $jobDefinition
     * @return \Illuminate\Http\Response
     */
    public function show(JobDefinition $jobDefinition)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JobDefinition  $jobDefinition
     * @return \Illuminate\Http\Response
     */
    public function edit(JobDefinition $jobDefinition)
    {
        //
        //dd('edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateJobDefinitionRequest  $request
     * @param  \App\Models\JobDefinition  $jobDefinition
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateJobDefinitionRequest $request, JobDefinition $jobDefinition)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JobDefinition  $jobDefinition
     * @return \Illuminate\Http\Response
     */
    public function destroy(JobDefinition $jobDefinition)
    {
        //
    }
}
