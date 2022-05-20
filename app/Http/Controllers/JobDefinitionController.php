<?php

namespace App\Http\Controllers;

use App\Constants\FileFormat;
use App\Constants\RoleName;
use App\Models\JobDefinition;
use App\Http\Requests\StoreJobDefinitionRequest;
use App\Http\Requests\UpdateJobDefinitionRequest;
use App\Models\User;
use Intervention\Image\Facades\Image;


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

        //image handling (custom error message to hide technical fields under image field)
        if ($request->isNotFilled('image_data_b64')) {
            return back()
                ->withErrors(['image' => __('validation.required', ['attribute' => 'image'])])->withInput();
        }
        $imageDataB64 = $request->image_data_b64;
        //Double-check extensions on base64 part
        if (!preg_match('~^data:[^/]+/(' . FileFormat::getImageFormatsAsRegex() . ');base64,~', $imageDataB64, $matches)) {
            return back()
                ->withErrors(['image' => __('Invalid image data, base64 expected with following extensions: ' .
                    FileFormat::getImageFormatsAsCSV())])->withInput();
        }

        $imageName = 'job-' . uniqid() . random_int(1, 2456) . '.'.FileFormat::JOB_IMAGE_TARGET_FORMAT;

        //Store image
        Image::make($imageDataB64)
            ->fit(FileFormat::JOB_IMAGE_WIDTH, FileFormat::JOB_IMAGE_HEIGHT)
            ->save(dmzStoragePath($imageName), null, FileFormat::JOB_IMAGE_TARGET_FORMAT);

        $newJob->image = $imageName;

        //Save to give an ID and then sync referenced tables
        $newJob->save();

        //Handle relations (id must have been attributed)
        $providers = User::role(RoleName::TEACHER)->whereIn('id', $request->providers)->pluck('id');
        $newJob->providers()->sync($providers);

        //Yeah, we made it ;-)
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
