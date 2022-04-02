<?php

namespace App\Http\Controllers;

use App\Models\JobDefinition;
use App\Http\Requests\StoreJobDefinitionRequest;
use App\Http\Requests\UpdateJobRequest;

class JobDefinitionController extends Controller
{

    public function __construct()
    {
        //map rbac authorization from policyClass
        $this->authorizeResource(JobDefinition::class,'jobDefinition');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): \Illuminate\Contracts\View\View
    {
        $definitions = JobDefinition::published()
            ->orderBy('required_xp_years')
            ->orderBy('priority')
            ->get();
        return view('job-definition')->with(compact('definitions'));
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
     * @param  \App\Models\JobDefinition  $job
     * @return \Illuminate\Http\Response
     */
    public function show(JobDefinition $job)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JobDefinition  $job
     * @return \Illuminate\Http\Response
     */
    public function edit(JobDefinition $job)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateJobRequest  $request
     * @param  \App\Models\JobDefinition  $job
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateJobRequest $request, JobDefinition $job)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JobDefinition  $job
     * @return \Illuminate\Http\Response
     */
    public function destroy(JobDefinition $job)
    {
        //
    }
}
