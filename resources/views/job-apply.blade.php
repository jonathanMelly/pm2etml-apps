<x-app-layout>
    @push('custom-scripts')
        @once
            <script type="text/javascript" src="{{ URL::asset ('js/helper.js') }}"></script>
        @endonce
    @endpush

    <div class="sm:mx-6 py-6 rounded-box bg-base-200 flex flex-col gap-2 items-center">

        <x-job-definition-card :job="$jobDefinition" :view-only="true" />

        @can('jobs-apply')

            <form method="post" action="{{ route('contracts.store') }}" class="w-auto">
                @csrf
                <input type="hidden" name="job_definition_id" value="{{$jobDefinition->id}}">
                <div class="flex flex-row gap-2 sm:min-w-max">
                    <div class="form-control flex flex-col gap-2">
                        <div class="label prose">
                            <h2 class="label-text">{{__('Application details')}}</h2>
                        </div>

                        <label class="input-group flex justify-between">
                            <div class="self-center justify-self-end">{{__('Start date')}}</div>
                            <input type="date" name="start_date" value="{{old('start_date')??now()->format(\App\DateFormat::HTML_FORMAT)}}"
                                   class=" input input-bordered input-primary">
                        </label>

                        <label class="input-group flex justify-between">
                            <div class="self-center justify-self-end">{{__('End date')}}</div>
                            <input type="date" name="end_date" value="{{old('end_date')??now()->addWeeks(3)->format(\App\DateFormat::HTML_FORMAT)}}"
                                   class=" input input-secondary input-bordered">
                        </label>

                        @php
                            $providers = $jobDefinition->getProviders();
                            $clients = $jobDefinition->getClients($providers);
                        @endphp

                        @foreach($parts as $part)
                            <label class="input-group">
                                @php
                                $size2="w-full";
                                if(!stringNullOrEmpty($part->name)){
                                    $size="w-1/3";
                                    $size2="w-2/3";
                                }
                                @endphp

                                @if(!stringNullOrEmpty($part->name))
                                    <span class="{{$size}}">{{$part->name}}</span>
                                @endif

                                <select class="select select-bordered {{$size2}}" name="client-{{$part->id}}">
                                    <option disabled selected>{{__('Client')}}</option>

                                    @foreach($providers as $client)
                                        <option value="{{$client->id}}" {{old('client')==$client->id?'selected="selected"':''}}>
                                            {{$client->firstname.' '.$client->lastname}} ({{$client->getClientLoad(\App\Models\AcademicPeriod::current())['percentage']}}%)
                                        </option>
                                    @endforeach
                                    <option class="divider p-0 m-0"></option>
                                    @foreach($clients as $client)
                                        <option value="{{$client->id}}" {{old('client')==$client->id?'selected="selected"':''}}>
                                            {{$client->firstname.' '.$client->lastname}} ({{$client->getClientLoad(\App\Models\AcademicPeriod::current())['percentage']}}%)
                                        </option>
                                    @endforeach
                                </select>

                            </label>
                        @endforeach

                        <button type="submit" class="btn btn-neutral w-36" onclick="spin('applyButton')">
                            <span id="applyButton" class="hidden"></span>
                            {{__('Apply')}}
                        </button>

                    </div>
                    @if($errors->any())
                        <div class="flex flex-col bg-error text-error-content rounded-box p-3 m-2 text-md max-h-fit self-center">
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
                </div>

            </form>
        @else
            <div class="alert alert-error w-auto">/!\ {{__('You cannot apply for a job')}}</div>
        @endcan

    </div>
</x-app-layout>
