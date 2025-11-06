<?php

namespace App\Http\Controllers;

use App\Constants\RoleName;
use App\Exceptions\DataIntegrityException;
use App\Http\Requests\StoreUpdateJobDefinitionRequest;
use App\Models\Attachment;
use App\Models\JobDefinition;
use App\Models\JobDefinitionMainImageAttachment;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class JobDefinitionController extends Controller
{
    public function __construct()
    {
        //map rbac authorization from policyClass
        $this->authorizeResource(JobDefinition::class, 'jobDefinition');
    }

    public function marketPlace(Request $request)
    {
        //For filters
        $providers = User::query()->whereHas('jobDefinitions')->get();

        $definitions = JobDefinition::query()
            //->where(fn($q)=>$q->published())
            ->filter($request) /*WARNING: filter must be FIRST to be able to handle trashed stuff*/
            ->where(fn ($q) => $q->available())
            ->whereNotIn('id', auth()->user()->contractsAsAWorker()->select('job_definition_id'))
            ->orderBy('required_xp_years')
            ->orderByDesc('one_shot')
            ->orderBy('priority')
            ->with('providers')
            ->with('image')
            ->with('attachments')
            ->with('skills.skillGroup')
            ->get();

        return view('marketplace')->with(compact('definitions', 'providers'));
    }

    /**
     * JSON content
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
    public function create(Request $request)
    {
        //ready for form reuse as edit...
        $job = new JobDefinition();

        return $this->createEdit($request, $job);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUpdateJobDefinitionRequest $request)
    {
        return $this->storeUpdate($request);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(JobDefinition $jobDefinition)
    {
        return view('jobDefinition-view')->with(compact('jobDefinition'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, JobDefinition $jobDefinition)
    {
        return $this->createEdit($request, $jobDefinition);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateJobDefinitionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(StoreUpdateJobDefinitionRequest $request, JobDefinition $jobDefinition)
    {
        return $this->storeUpdate($request, $jobDefinition);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Application|\Illuminate\Foundation\Application|RedirectResponse|Redirector
     */
    public function destroy(JobDefinition $jobDefinition, User $user)
    {
        $jobDefinition->delete();

        Log::info("Job with id {$jobDefinition->id} deleted by user with id {$user->id}");

        return redirect(route('marketplace'))
            ->with('success', __('Job ":job" deleted', ['job' => $jobDefinition->title]));
    }

    protected function createEdit(Request $request, JobDefinition $jobDefinition)
    {
        //First arrival on form, we store the url the user comes from (to redirect after success)
        if (! Session::hasOldInput()) {
            Session::put('start-url', $request->header('referer'));
        }

        $providers = User::role(RoleName::TEACHER)
            ->orderBy('firstname')
            ->orderBy('lastname')
            //Skip current user as it will be added on top
            ->where('id', '!=', auth()->user()->id)
            ->get(['id', 'firstname', 'lastname']);

        [$pendingAndOrCurrentAttachments, $pendingOrCurrentImage] =
            $this->extractAttachmentState($jobDefinition);

        $availableSkills = Skill::query()
            ->whereNotIn(tbl(Skill::class).'.id', $jobDefinition->skills->pluck('id'))
            ->with('skillGroup')
            ->get();

        //Force eager load on object given by Laravel on route ...
        $jobDefinition->load('skills.skillGroup');

        $initialTimeInPeriod = old('allocated_time', $jobDefinition->getAllocatedTime());
        if ($initialTimeInPeriod == 0) {
            $initialTimeInPeriod = JobDefinition::MIN_PERIODS;
        }

        return view('jobDefinition-create-update')
            ->with(compact(
                'providers',
                'pendingAndOrCurrentAttachments',
                'pendingOrCurrentImage', 'availableSkills', 'initialTimeInPeriod'))
            ->with('job', $jobDefinition);
    }

    protected function storeUpdate(StoreUpdateJobDefinitionRequest $request,
        ?JobDefinition $jobDefinition = null)
    {
        $editMode = $jobDefinition != null;

        //Group job, attachment,providers in same unit
        DB::transaction(function () use ($request, &$jobDefinition) {
            //Save to give an ID and then sync referenced tables
            if ($jobDefinition == null) {
                //Use mass assignment ;-)
                $jobDefinition = JobDefinition::create($request->all());
            } else {
                $data = $request->all();

                //Draft is handled with published_date, thus must be handled manually
                if (! $request->exists('publish')) {
                    $data['published_date'] = null;
                }

                //Oneshot is a toggle thus must be handled manually
                if (! $request->exists('one_shot')) {
                    $data['one_shot'] = false;
                }
                // ... so is by_application
                if (! $request->exists('by_application')) {
                    $data['by_application'] = false;
                }
                $jobDefinition->update($data);
            }

            //First delete any removed attachments (including image)
            $attachmentIdsToDelete = json_decode($request->input('any_attachment_to_delete'));
            foreach (Attachment::findMany($attachmentIdsToDelete) as $attachment) {
                $attachment->delete();
            }

            //Image
            $image = $request->input('image');
            if ($jobDefinition->image == null || $jobDefinition->image->id != $image) {
                $image = JobDefinitionMainImageAttachment::findOrFail($image);
                if ($image->attachable_id != null) {
                    throw new DataIntegrityException('Image already linked to another job');
                }
                $image->attachJobDefinition($jobDefinition);
            }

            //PROVIDERS
            //Handle relations (id must have been attributed)
            $providers = User::role(RoleName::TEACHER)
                ->whereIn('id', $request->input('providers'))
                ->pluck('id');
            $syncResult = $jobDefinition->providers()->sync($providers);
            if (collect($syncResult)->transform(fn ($k) => count($k))->sum() > 0) {
                Cache::forget('providers-'.$jobDefinition->id);
                Cache::forget('clients-'.$jobDefinition->id);
            }

            //Attachments (already uploaded, we just bind them)
            $attachmentIds = json_decode($request->input('other_attachments'));
            foreach (Attachment::findMany(collect($attachmentIds)->values()) as $attachment) {
                $attachment->attachJobDefinition($jobDefinition);
            }

            //Skills
            $submittedSkills = collect(json_decode($request->input('skills')));
            $skillIds = $submittedSkills
                ->transform(fn ($el) => Skill::firstOrCreateFromString($el))
                ->pluck('id');
            $jobDefinition->skills()->sync($skillIds);

        });

        //Yeah, we made it ;-)
        $targetUrl = Session::get('start-url') ?? route('marketplace');

        return redirect()->to($targetUrl)
            ->with('success', __('Job ":job" '.($editMode ? 'updated' : 'created'), ['job' => $jobDefinition->title]));

    }

    protected function extractAttachmentState(JobDefinition $jobDefinition): array
    {
        $old = old('other_attachments');
        if ($old == null) {
            $pendingAttachments = $jobDefinition->attachments->pluck('id');
        } else {
            $pendingAttachments = collect(json_decode($old))->values();
        }
        $pendingAndOrCurrentAttachments =
            \App\Models\JobDefinitionDocAttachment::findMany($pendingAttachments);

        $pendingOrCurrentImage =
            \App\Models\JobDefinitionMainImageAttachment::find(old('image',
                $jobDefinition->image?->id));

        return [$pendingAndOrCurrentAttachments, $pendingOrCurrentImage];
    }
}
