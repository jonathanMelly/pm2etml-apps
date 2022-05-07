@php
    $editMode = $job->exists;
    $user = auth()->user();
@endphp
<x-app-layout>
    @push('custom-scripts')
        <script>

            //(typeof files[i])
            //files[i].name
            //files[i].size
            function dodrop(event) {
                let dt = event.dataTransfer;
                let files = dt.files;

                return files;
            }

            function doDropImage(event) {
                let image = dodrop(event)[0];

                if (/\.(jpg|jpeg|gif|svg|png|tiff)$/.test(image.name)) {
                    document.querySelector('#image-preview').src = window.URL.createObjectURL(image);
                    document.querySelector('#image_data').value = image.name;
                }

            }


            //Idea to keep image...
            {{--
            const input = document.getElementById("selectAvatar");
            const avatar = document.getElementById("avatar");
            const textArea = document.getElementById("textAreaExample");

            const convertBase64 = (file) => {
                return new Promise((resolve, reject) => {
                    const fileReader = new FileReader();
                    fileReader.readAsDataURL(file);

                    fileReader.onload = () => {
                        resolve(fileReader.result);
                    };

                    fileReader.onerror = (error) => {
                        reject(error);
                    };
                });
            };

            const uploadImage = async (event) => {
                const file = event.target.files[0];
                const base64 = await convertBase64(file);
                avatar.src = base64;
                textArea.innerText = base64;
            };

            input.addEventListener("change", (e) => {
                uploadImage(e);
            });
             */
        --}}
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
                            {{__('Name')}}
                        </label>
                        <input class="input w-full @error('name') border-error @enderror" id="name" name="name"
                               type="text" placeholder="PVMaker : Générateur de procès-verbal"
                               value="{{old('name',$job->name)}}">
                        @error('name')
                        <p class="text-error text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2"
                               for="required_xp_years">
                            {{__('Target audience')}}
                        </label>
                        <select class="select w-full" name="required_xp_years" id="required_xp_years">
                            @for($i=1;$i<5;$i++)
                                <option
                                    value="{{$i-1}}" @selected(old('required_xp_years',$job->required_xp_years)===$i-1)>
                                    {!!$i.'<sup>'.__(ordinal($i)).'</sup>&nbsp;'.__('year')!!}</option>
                            @endfor
                        </select>

                    </div>

                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2"
                               for="priority">
                            {{__('Priority')}}
                        </label>
                        <div class="relative">
                            <select class="select w-full" name="priority" id="priority">
                                @foreach(\App\Enums\JobPriority::cases() as $priority)
                                    <option
                                        value="{{$priority->value}}" @selected($editMode  &&  old('priority',$job->priority->value)===$priority->value)>
                                        {{__(Str::ucfirst(Str::lower($priority->name)))}}</option>
                                @endforeach
                            </select>
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
                        <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2"
                               for="image">
                            {{__('Image')}}
                        </label>

                        <div id="image-drop"
                             class="p-2 rounded-box border-base-300 border-2 border-dashed min-h-[15rem] max-h-min"
                             ondragenter="this.classList.add('bg-accent');event.stopPropagation(); event.preventDefault();"
                             ondragover="event.stopPropagation(); event.preventDefault();"
                             ondragleave="this.classList.remove('bg-accent')"
                             ondrop="this.classList.remove('bg-accent');event.stopPropagation(); event.preventDefault();doDropImage(event);">

                            <label for="image_data" class="hover:cursor-pointer text-center">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <strong>{{__('Choose a file')}}</strong> <span>{{__('or drag it here')}}</span>.
                            </label>
                            <input class="hidden" id="image_data" name="image_data" type="file" accept="image/*"
                                   onchange="document.querySelector('#image-preview').src=window.URL.createObjectURL(event.target.files[0]);"
                                   value="{{$job->image}}"
                            >

                            <img id="image-preview" class="min-h-[7rem] max-h-min"
                                 src="{{$editMode?dmzImgUrl($job->image):''}}">
                            {{old('image_data')}}
                            @error('image_data')
                            <p class="text-error text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                        </div>
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

                <div class="flex flex-col -mx-3 mb-6"
                     x-data="{time:Math.max({{old('allocated_time',$job->getAllocatedTime())}},20)}">
                    <div class="w-full px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2"
                               for="allocated_time-display">
                            {{__('Allocated time')}}
                        </label>
                        <input id="allocated_time-display" type="range" min="2" max="300" x-model="time" class="range"/>

                    </div>

                    <div class="flex flex-row items-center self-center place-items-center">
                        <input class="input w-24 text-lg font-bold" name="allocated_time" min="2" max="300"
                               type="number" x-model="time">
                        <div class="pl-1 font-bold text-lg">{{__('periods')}}</div>
                        <div class="ml-1">
                            (<span x-text="Math.round(time*45/60)"></span> {{__('hours')}})
                        </div>
                    </div>
                </div>

                <div class="flex flex-col -mx-3 mb-6">
                    <div class="w-full px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-base-content text-xs font-bold mb-2"
                               for="one_shot">
                            {{__('One shot')}}
                        </label>
                        <input @checked(old('one_shot',$job->one_shot)) id="one_shot" name="one_shot" type="checkbox"
                               class="toggle" value="1"/>
                        <p class="text-accent-content text-sm italic">
                            {{__('One shot means that as soon as a worker applies for the job, the latter won’t be available to others anymore')}}
                        </p>
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
                        <input name="publish" type="checkbox" class="toggle"@checked($job->isPublished())/>
                        <p class="text-accent-content text-sm italic">{{__('Only published jobs are shown in the marketplace')}}</p>
                    @else
                        <button onclick="document.querySelector('#create-update-form').submit()"
                                name="draft" value="true" class="btn btn-warning bg-opacity-75 hover:bg-opacity-100 my-2"
                                type="button">{{__('Save as draft')}}</button>
                    @endif
                </div>

                <div class="divider divider-horizontal">{{__($editMode?'':'OR')}}</div>
                <div class="grid h-20 flex-grow card rounded-box justify-items-start content-center">
                    <button name="createOrSave" class="btn btn-success btn-outline my-2"
                            onclick="document.querySelector('#published_date').value='{{now()}}';
                                document.querySelector('#create-update-form').submit()"
                            type="button">{{__($editMode?'Save modifications':'Publish job offer')}}</button>
                </div>
            </div>
        </div>


        </div>


    </form>

</x-app-layout>
