<?php

namespace App\Http\Controllers;

use App\Constants\RoleName;
use App\Models\Contract;
use App\Models\JobDefinition;
use App\Http\Requests\StoreJobDefinitionRequest;
use App\Http\Requests\UpdateJobDefinitionRequest;
use App\Models\User;


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
        //ready for form reuse as edit...
        $job = new JobDefinition();
        return $this->createEdit($job);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreJobDefinitionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreJobDefinitionRequest $request)
    {
        //Use mass assignment ;-)
        $newJob = JobDefinition::make($request->all());

        //image handling
        //TODO use base64 in client for easy dragndrop + validation errors
        $imageName = 'job-'.uniqid().random_int(1,2456).'.'.$request->image_data->extension();
        $request->image_data
            //->resize(350, 350, fn ($constraint) => $constraint->aspectRatio())
            ->move(dmzStoragePath(), $imageName);
        $newJob->image = $imageName;

        $newJob->save();

        //Handle relations (id must have been attributed)
        $newJob->providers()->sync($request->providers);

        return redirect(route('marketplace'))
            ->with('success',__('Job ":job" created',['job'=>$newJob->name]));
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
        return $this->createEdit($jobDefinition);
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
        $jobDefinition->delete();
        return redirect(route('marketplace'))
            ->with('success',__('Job ":job" deleted',['job'=>$jobDefinition->name]));
    }

    protected function createEdit($jobDefinition)
    {
        $providers = User::role(RoleName::TEACHER)
            ->orderBy('firstname')
            ->orderBy('lastname')
            //Skip current user as it will be added on top
            ->where('id','!=',auth()->user()->id)
            ->get(['id','firstname','lastname']);

        return view('jobDefinition-create-update')
            ->with(compact('providers'))
            ->with('job',$jobDefinition);
    }
}
