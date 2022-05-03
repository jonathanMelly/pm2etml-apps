<x-app-layout>

    <div class="sm:mx-6 py-6 rounded-box bg-base-200 flex flex-col gap-2 items-center">

        <x-job-definition-card :job="$jobDefinition" :view-only="true" />

        @can('jobs-apply')

            <form method="post" action="{{ route('contracts.store') }}" class="w-auto">
                @csrf
                <input type="hidden" name="job_definition_id" value="{{$jobDefinition->id}}">
                <div class="flex flex-row gap-2 sm:min-w-max">
                    <div class="form-control flex flex-col gap-2 items-center">
                        <label class="label prose">
                            <h2 class="label-text">{{__('Application details')}}</h2>
                        </label>

                        <label class="input-group">
                            <span class="w-1/2">{{__('Start date')}}</span>
                            <input type="date" name="start_date" value="{{old('start_date')??now()->format('Y-m-d')}}"
                                   class="w-1/2 input input-bordered input-primary">
                        </label>

                        <label class="input-group">
                            <span class="w-1/2">{{__('End date')}}</span>
                            <input type="date" name="end_date" value="{{old('end_date')??now()->addWeeks(3)->format('Y-m-d')}}"
                                   class="w-1/2 input input-secondary input-bordered">
                        </label>

                        <div class="input-group">
                            <select class="select select-bordered w-2/3" name="client">
                                <option disabled selected>{{__('Client')}}</option>
                                @foreach($jobDefinition->providers as $client)
                                    <option value="{{$client->id}}" {{old('client')==$client->id?'selected="selected"':''}}>{{$client->firstname.' '.$client->lastname}}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn w-1/3" onclick="this.classList.add('loading')">{{__('Apply')}}</button>
                        </div>
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
