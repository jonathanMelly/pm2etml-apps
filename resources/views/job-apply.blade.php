<x-app-layout>
    @push('custom-scripts')
        @once
            @vite(['resources/js/helper.js'])
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

                        @if ($jobDefinition->by_application)
                        <x-job-application-wish />
                        @endif

                        <x-client-select :job-definition="$jobDefinition" :parts="$parts" />

                        <button type="submit" class="btn btn-neutral w-36 place-self-center" onclick="spin('applyButton')">
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
