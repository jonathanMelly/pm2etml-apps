@php
    $editMode = $job->exists;
    $user = auth()->user();
    $dzI18N='dictDefaultMessage  : "'.__('Choose a file').' '.__('or drag it here').'",
        dictFallbackMessage : "'.__("Your browser does not support drag'n'drop file uploads.").'",
        dictFallbackText    : "'.("Please use the fallback form below to upload your files like in the olden daysaaa.").'",
        dictFileTooBig      : "'.__("File is too big ({{filesize}} MiB). Max filesize: {{maxFilesize}} MiB.").'",
        dictInvalidFileType : "'.__("You can't upload files of this type.").'",
        dictResponseError   : "'.__("Server responded with {{statusCode}} code.").'",
        dictCancelUpload    : "'.__("Cancel upload").'",
        dictCancelUploadConfirmation : "'.__("Are you sure you want to cancel this upload?").'",
        dictRemoveFile       : "'.__("Remove file").'",
        dictMaxFilesExceeded : "'.__("You can not upload any more files.").'"
        ';
@endphp
<x-app-layout>
    @push('custom-scripts')

        <link rel="stylesheet" href="{{ asset('css/dropzone.css') }}">
        <style>
            {{-- Fix error tooltip position --}}
            .dropzone .dz-preview .dz-error-message {
                top: 150px !important;
            }

            {{-- Disable on max reached to avoid bad sides effects... --}}
            .dz-max-files-reached {
                pointer-events: none;
                cursor: default;
            }

            .dz-remove {
                pointer-events: all;
                cursor: default;
            }

            .dropzone .dz-preview.dz-image-preview {
                background: 0;
            }

        </style>
        @vite('resources/js/dropzone.js')

        <script type="module">
            {{-- WARNING: the main idea is : what has already been saved is not deleted directly (waits for save button)... --}}
            {{-- Reloads from old if any errors happened (and happen again), we keep what user asked until then... --}}
            let otherAttachments = JSON.parse('{!!old('other_attachments','{}')!!}');
            let anyAttachmentsToDelete = JSON.parse("{{old('any_attachment_to_delete','[]')}}");
            let image = '{{old('image',$job->image!=null?$job->image->id:'')}}';{{-- always send image id... --}}

            function updateFormFields() {
                document.querySelector('[name=other_attachments]').value =
                    JSON.stringify(otherAttachments);

                document.querySelector('[name=image]').value = image;

                document.querySelector('[name=any_attachment_to_delete]').value =
                    JSON.stringify(anyAttachmentsToDelete);
            }

            let imageZone;
            let attachmentsZone;

            window.addEventListener('DOMContentLoaded', (event) => {

                {{-- Update data regarding optional old values--}}
                updateFormFields();

                attachmentsZone = new Dropzone("#attachments", {
                    url: "{{route('job-definition-doc-attachment.store')}}",
                    maxFilesize: {{\App\Constants\FileFormat::JOB_ATTACHMENT_MAX_SIZE_IN_MO}},
                    acceptedFiles: "{{\App\Constants\FileFormat::getFileFormatsAsCSV(\App\Constants\FileFormat::JOB_DOC_ATTACHMENT_ALLOWED_EXTENSIONS,true)}}",
                    uploadMultiple: false,
                    parallelUploads: 5,
                    maxFiles: {{\App\Constants\FileFormat::JOB_ATTACHMENT_MAX_COUNT}},
                    headers: {
                        'x-csrf-token': '{{csrf_token()}}'
                    },
                    addRemoveLinks: true,
                    {!! $dzI18N !!},

                    init: function () {
                        this.on("addedfile", function (file) {
                            {{--
                            //TODO hack CSS to put nice image + filename/size...
                            //This is fun but needs some CSS tricks to show file name + size
                            //as the thumbnail hides them by default...
                            if (!file.type.match(/image.*/)) {

                                //var ext = file.name.split('.').pop();

                                /*
                                let type = file.type;

                                if(type.includes('pdf'))
                                {
                                    this.emit("thumbnail", file, "/img/pdf.svg");
                                }
                                else if(type.includes('excel') || type.includes('calc'))
                                {
                                    this.emit("thumbnail", file, "/img/excel.svg");
                                }
                                else if(type.includes('word'))
                                {
                                    this.emit("thumbnail", file, "/img/word.svg");
                                }
                                else
                                {
                                    this.emit("thumbnail", file, "/img/file.svg");
                                }


                            }
                            --}}
                        });

                        this.on("removedfile", function (file) {
                            {{-- Save delete info for editController if user confirms it...
                                CorrelationId means that it already hase been saved before for that job definition...
                             --}}
                            if (file.hasOwnProperty('correlation_id')) {
                                {{--
                                User wants to remove previous image but has not yet saved changes (button click)...
                                We do not delete the attachment now and wait for editController to do it
                                (which let user change his mind without loosing anything)
                                --}}
                                anyAttachmentsToDelete.push(file.correlation_id);
                                Object.entries(otherAttachments).forEach(function ([filename, id]) {
                                    if (id == file.correlation_id) {
                                        delete otherAttachments[filename];
                                    }
                                });
                            } else if (otherAttachments.hasOwnProperty(file.name)) {
                                axios.delete('{{url('attachments')}}/' + otherAttachments[file.name]);
                                delete otherAttachments[file.name];
                            }

                            updateFormFields();
                        });

                        this.on("success", function (file, response) {
                            otherAttachments[file.name] = response['id'];
                            updateFormFields();
                        });
                        this.on("error", function (file, response) {
                            if (typeof response == "object")//comes from xhr or local stuff ?
                            {
                                file.previewElement.querySelectorAll('.dz-error-message span')[0].textContent = response['error'] ?? response['message'] ?? JSON.stringify(response);
                            }

                        });

                        {{-- Show pending/current ATTACHMENTS --}}
                        @foreach($pendingAndOrCurrentAttachments as $docAttachment)
                        let mockFile{{$loop->index}} = {
                            name: "{{$docAttachment->name}}",
                            size: {{$docAttachment->size}},
                            correlation_id: {{$docAttachment->id}}
                        };
                        this.emit("addedfile", mockFile{{$loop->index}});
                        this.emit("complete", mockFile{{$loop->index}});

                        //this.options.maxFiles--;
                        @endforeach

                    }
                });

                imageZone = new Dropzone("#image-dz", {
                    url: "{{route('job-definition-main-image-attachment.store')}}",
                    maxFilesize: {{\App\Constants\FileFormat::JOB_ATTACHMENT_MAX_SIZE_IN_MO}},
                    acceptedFiles: "{{\App\Constants\FileFormat::getImageFormatsAsCSV(true)}}",
                    uploadMultiple: false,
                    maxFiles: 1,
                    headers: {
                        'x-csrf-token': '{{csrf_token()}}'
                    },
                    addRemoveLinks: true,
                    {!! $dzI18N !!},

                    init: function () {
                        this.on("removedfile", function (file) {
                            if (file.hasOwnProperty('correlation_id')) {
                                {{--
                                User wants to remove previous image but has not yet saved changes (button click)...
                                We do not delete the attachment now and wait for editController to do it
                                (which let user change his mind without loosing anything)
                                --}}
                                anyAttachmentsToDelete.push(file.correlation_id);
                                image = '';
                            }
                            else if (image != '') {
                                axios.delete('{{url('attachments')}}/' + image);
                                image = '';
                            }
                            updateFormFields();
                        });

                        this.on("success", function (file, response) {
                            image = response['id'];
                            updateFormFields();
                        });

                        this.on("error", function (file, response) {
                            if (typeof response == "object")//comes from xhr or local stuff ?
                            {
                                file.previewElement.querySelectorAll('.dz-error-message span')[0].textContent = response['error'] ?? response['message'] ?? JSON.stringify(response);
                            }

                        });

                        {{-- Show pending/current IMAGE --}}
                        @php
                            //old has priority (=change asked)

                        @endphp

                        @if($pendingOrCurrentImage!=null)
                        let pendingOrCurrentImage = {
                            name: "{{$pendingOrCurrentImage->name}}",
                            size: {{$pendingOrCurrentImage->size}},
                            correlation_id: {{$pendingOrCurrentImage->id}}
                        };

                        this.displayExistingFile(pendingOrCurrentImage, "{{attachmentUri($pendingOrCurrentImage)}}");
                        {{-- Only 1 imae file allowed... --}}
                        document.querySelector('#image-dz').classList.add('dz-max-files-reached');
                        @endif

                    }
                });

            });

        </script>
    @endpush

    <div class="prose pb-2 -p-6 sm:mx-6">
        <h1 class="text-base-content">{{__($editMode?'Edit job':'Create a new job')}}</h1>
    </div>

    @if($errors->any())
        <div
                class="flex flex-col -p-6 sm:mx-6 bg-error text-error-content rounded-box p-3 m-2 text-md max-h-fit self-center">
            <div class="self-center">
                {{__('Please fix the following issues')}}:
            </div>
            <ul class="mt-3 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li class="text-error-content/75">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="create-update-form" x-on:submit.prevent
          action="{{$editMode?route('jobDefinitions.update',$job):route('jobDefinitions.store')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        @if($editMode)
            @method('put')
        @endif
        <div class="sm:mx-6 bg-base-100 bg-opacity-50 rounded-box sm:p-3 p-1 flex flex-col items-center">

            {{-- FORM DETAILS --}}
            <div
                    class="bg-base-300 bg-opacity-50 border border-accent border-opacity-50 rounded-box p-3 w-full flex flex-col sm:gap-y-6">

                <div class="flex flex-wrap -mx-3 mb-2">
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2"
                               for="name">
                            {{__('Title')}}
                        </label>
                        <input class="input w-full @error('title') border-error @enderror" id="title" name="title"
                               type="text" placeholder="PVMaker : Générateur de procès-verbal"
                               value="{{old('title',$job->title)}}">
                        @error('title')
                        <p class="text-error text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2"
                               for="required_xp_years">
                            {{__('Target audience')}}
                        </label>
                        <x-job-select-xp-years class="w-full" :old="old('required_xp_years',$job->required_xp_years)" />

                    </div>

                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2"
                               for="priority">
                            {{__('Priority')}}
                        </label>
                        <div class="relative">
                            <x-job-select-priority class="w-full" :old="old('priority',$editMode?$job->priority->value:-1)" />
                        </div>

                    </div>

                </div>

                <div class="flex flex-wrap -mx-3 mb-2">
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2"
                               for="description">
                            {{__('Description')}}
                        </label>
                        <textarea id="description"
                                  class="textarea w-full min-h-[15rem] @error('description') border-error @enderror"
                                  name="description"
                                  placeholder="PVMaker est un outil WEB pour générer et stocker des procès-verbaux..."
                        >{{old('description')}}@if($editMode){{old('description',$job->description)}}@endif</textarea>
                        @error('description')
                        <p class="text-error text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <input type="hidden" name="image">
                        <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2"
                               for="image-dz">
                            {{__('Image')}}
                        </label>
                        <div class="dropzone dropzone-file-area dropzone-img !rounded-box !border-base-300 !border-2 !border-dashed !min-h-[15rem] !max-h-min"
                             id="image-dz" style="text-align: center">
                            <div class="dz-message" data-dz-message><i class="fa-solid fa-cloud-arrow-up"></i>
                                <strong>{{__('Choose one file')}} (image)</strong>
                                <span>{{__('or drag it here')}}</span>.
                            </div>

                        </div>
                        @error('image')
                        <p class="text-error text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2"
                               for="providers">
                            {{__('Providers')}}
                        </label>
                        <select class="select w-full min-h-[15rem]" id="providers" name="providers[]" multiple>
                            <option value="{{$user->id}}" selected>{{$user->firstname . ' ' . $user->lastname}}</option>
                            @php
                                $oldProviders = $editMode?$job->providers->pluck('id')->toArray():old('providers');
                            @endphp
                            @foreach($providers as $provider)
                                <option
                                        value="{{$provider->id}}" @selected($oldProviders!==null && in_array($provider->id,$oldProviders)) >
                                    {{$provider->firstname . ' ' . $provider->lastname}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap -mx-3 mb-2"
                     x-data="{time:{{$initialTimeInPeriod}}}">
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0 flex flex-col place-items-center">
                        <div class="w-full">
                            <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2"
                                   for="allocated_time-display">
                                {{__('Workload')}}
                            </label>
                            <input id="allocated_time-display" type="range"
                                   min="{{\App\Models\JobDefinition::MIN_PERIODS}}"
                                   max="{{\App\Models\JobDefinition::MAX_PERIODS}}" x-model="time" class="range"
                                   step="{{round((\App\Models\JobDefinition::MAX_PERIODS-\App\Models\JobDefinition::MIN_PERIODS)/2)}}"/>
                            <div class="w-full flex justify-between text-xs px-2">
                                <span>{{__('Small')}}</span>
                                <span>{{__('Medium')}}</span>
                                <span>{{__('Large')}}</span>
                            </div>
                        </div>
                        <div class="flex flex-row place-items-center mt-2">
                            <input class="input w-24 text-lg font-bold" name="allocated_time"
                                   min="{{\App\Models\JobDefinition::MIN_PERIODS}}"
                                   max="{{\App\Models\JobDefinition::MAX_PERIODS}}"
                                   type="number" x-model="time" value="{{$initialTimeInPeriod}}{{-- This will be overwritten by alpinejs but provides a way to test initial value --}}">
                            <div class="pl-1 font-bold text-lg">{{__('periods')}}</div>
                            <div class="ml-1">
                                (<span x-text="Math.round(time*45/60)">{{$job->getAllocatedTime(\App\Enums\RequiredTimeUnit::HOUR)}}{{-- This will be overwritten by alpinejs but provides a way to test initial value --}}</span> {{__('hours')}})
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2"
                               for="one_shot">
                            {{__('Particularities')}}
                        </label>
                        <div class="w-full px-3 mb-6 md:mb-0 mt-1">
                            <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2"
                                   for="one_shot">
                                {{__('One shot')}}
                            </label>
                            <input @checked(old('one_shot',$job->one_shot)) id="one_shot" name="one_shot"
                                   type="checkbox"
                                   class="toggle" value="1"/>
                            <p class="text-accent-content text-sm italic">
                                {{__('One shot means that as soon as a worker applies for the job, the latter won’t be available to others anymore')}}
                            </p>
                        </div>

                        <div class="w-full px-3 mb-6 md:mb-0 mt-1">
                            <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2"
                                   for="by_application">
                                {{__('By application')}}
                            </label>
                            <input @checked(old('by_application',$job->by_application)) id="by_application" name="by_application"
                                   type="checkbox"
                                   class="toggle" value="1"/>
                            <p class="text-accent-content text-sm italic">
                                {{__('By application means that instead of hiring, we simply take note of the wish to be hired for the job')}}
                            </p>
                        </div>
                    </div>
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0 flex flex-col"
                         x-data="{
                            skills:{{old('skills',$job->skills->transform(fn($skill)=>$skill->getFullName())->toJson())}},
                            skill:'',
                            skillIsValid()
                            {
                                return this.skill!=null && this.skill.match(/.+:.+/) && !this.skills.includes(this.skill);
                            },
                            addSkill()
                            {
                                if(this.skillIsValid())
                                {
                                    this.skills.push(this.skill);
                                    this.skill='';
                                }
                            }
                         }">
                        <div class="w-full">
                            <div class="flex flex-col">
                                <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2">
                                    {{__('Skills')}}

                                    <div class="dropdown dropdown-hover hover:cursor-help">
                                        <label tabindex="0" class="ml-1"><i
                                                    class="fa-solid fa-info-circle fa-xl hover:cursor-help"></i></label>
                                        <ul tabindex="0"
                                            class="dropdown-content text-xs menu p-2 shadow bg-base-100 rounded-box w-80 max-h-80 overflow-y-auto">
                                            <li class="italic normal-case">{{__("Besides existing skills below, you can add any custom one in format")}} {{__('SKILL_GROUP')}}{{\App\Models\Skill::SEPARATOR}}{{__('SKILL_NAME')}}</li>
                                            <li></li>
                                            @foreach($availableSkills as $skill)
                                                <li class="whitespace-nowrap normal-case my-0 py-0">
                                                    <a class="py-1 my-0" @click="skill='{{$skill->getFullName()}}';addSkill()">{{$skill->getFullName()}}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </label>

                                <input type="hidden" name="skills" x-model="JSON.stringify(skills)" value="">
                                <div class="flex flex-row">
                                    {{-- TODO: handle click on datalist element to add item --}}
                                    <input class="input w-4/5 @error('skills') border-error @enderror"
                                           id="skills-search"
                                           type="text" placeholder="devops:integration continue" list="skills-list"
                                           @keyup.enter="addSkill()"
                                           @keydown.tab.prevent="addSkill()" x-model="skill">
                                    <datalist id="skills-list">
                                        @foreach($availableSkills as $skill)
                                            <option value="{{$skill->getFullName()}}"/>
                                        @endforeach
                                    </datalist>
                                    <button :class="{'btn-disabled' : !skillIsValid()}" @click="addSkill()" class="btn w-1/5 ml-2" type="button"><i class="fa-solid fa-add"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap mt-2">
                            <template x-for="skill in skills">
                                <div class="badge badge-neutral badge-outline gap-2 mx-1 my-1">
                                    <span x-text="skill"></span>
                                    <svg @click="skills=skills.filter(_=>_!=skill)"
                                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                         class="inline-block w-4 h-4 stroke-current hover:cursor-pointer">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </template>
                            <template x-if="skills==null || skills.length==0">
                                <div>{{__('Type a skill and add it (ENTER or click on +)')}}</div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- ATTACHMENTS --}}
                <input type="hidden" name="other_attachments"/>
                <input type="hidden" name="any_attachment_to_delete"/>
                <div class="flex flex-col -mx-3 mb-6">
                    <div class="w-full px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2"
                               for="attachments">
                            {{__('Attachments')}} ({{__('Specifications')}}, etc.)
                        </label>
                        <div class="dropzone dropzone-file-area !rounded-box !border-base-300 !border-2 !border-dashed"
                             id="attachments">
                            <div class="dz-message" data-dz-message><i class="fa-solid fa-cloud-arrow-up"></i>
                                <strong>{{__('Choose one or more file(s)')}} (pdf, excel, word)</strong>
                                <span>{{__('or drag them here')}}</span>.
                            </div>

                        </div>
                    </div>

                </div>

            </div>

            {{-- Submit buttons --}}
            <input type="hidden" name="published_date" id="published_date">
            <div class="flex w-full my-2">
                <div class="grid h-20 flex-grow card rounded-box justify-items-end content-center">
                    @if($editMode)
                        <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2"
                               for="grid-password">
                            {{__('Publish')}}
                        </label>
                        <input name="publish" type="checkbox" class="toggle" @checked($job->isPublished()) >
                        <p class="text-accent-content text-sm italic">{{__('Only published jobs are shown in the marketplace')}}</p>
                    @else
                        <button onclick="document.querySelector('#create-update-form').submit()"
                                name="draft" value="true"
                                class="btn btn-warning bg-opacity-50 hover:bg-opacity-100 my-2"
                                type="button">{{__('Save as draft')}}</button>
                    @endif
                </div>

                <div class="divider divider-horizontal">{{__($editMode?'':'OR')}}</div>
                <div class="grid h-20 flex-grow card rounded-box justify-items-start content-center">
                    <button name="createOrSave" class="btn btn-success bg-opacity-75 hover:bg-opacity-100 my-2"
                            onclick="
                                    document.querySelector('#published_date').value='{{now()}}';
                                    this.closest('form').submit()"
                            type="button">{{__($editMode?'Save modifications':'Publish job offer')}}</button>
                    {{-- Cancel not shown to avoid any bad click... if user closes the window without saving, changes are lost...
                    <a href  class="btn btn-error btn-outline my-2">{{__($editMode?'Cancel modifications':'Cancel job offer')}}</a>
                     --}}
                </div>
            </div>
        </div>


        </div>


    </form>

</x-app-layout>
