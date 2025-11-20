@php
    $involvedGroupNames = auth()->user()->involvedGroupNames($periodId);

    $showGroupFilterJsEval=$involvedGroupNames->map(fn($e)=>'showGroup_'.$e)->join(' && ');
@endphp
<div class="overflow-x-auto w-full"
     x-data="{contracts:'',hideAlreadyEvaluated:$persist(false){{$involvedGroupNames->count()>0?",".($involvedGroupNames->map(fn($e)=>'showGroup_'.$e.':$persist(true)')->join(',')):""}}}">

    <div class="form-control flex flex-row">
        <div class="self-center ml-1 text-sm">{{__('Show following groups :')}}</div>
        <div class="flex flex-wrap">
        @foreach($involvedGroupNames as $involvedGroupName)
            <label class="cursor-pointer label">
                <span class="label-text ml-1">{{$involvedGroupName}}</span>

                <input type="checkbox" class="toggle toggle-primary ml-1"
                       @click="showGroup_{{$involvedGroupName}}=!showGroup_{{$involvedGroupName}};
                   if(!showGroup_{{$involvedGroupName}}){toggleCheckBoxes('job-',false,true)};updateProjectsVisbility();"
                       :checked="showGroup_{{$involvedGroupName}}"/>
            </label>
        @endforeach
        </div>

    </div>

    <div class="form-control flex flex-row">
        <label class="cursor-pointer label">
            <span class="label-text">{{__('Hide already evaluated contracts')}}</span>
            <input type="checkbox" class="toggle toggle-accent ml-1"
                   @click="hideAlreadyEvaluated=!hideAlreadyEvaluated;
                   if(hideAlreadyEvaluated){toggleCheckBoxes('job-',false,true)};updateProjectsVisbility();"
                   :checked="hideAlreadyEvaluated"/>
        </label>
    </div>

    <div class="form-control flex flex-row">
        <button class="btn btn-outline btn-neutral btn-xs"
                id="addContractButtonMain"
                onclick="addContractMain.showModal()">
            <i class="fa-solid fa-user-plus"></i>{{__('Add a contract')}}
        </button>
        <dialog id="addContractMain" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg">{{__('Add a contract')}}</h3>
                <form method="post" action="{{route('contracts.store')}}"
                      id="addContract-Main-form"
                      name="addContract-Main-form">
                    @method('POST')
                    @csrf

                    <label>
                        {{__('Job')}}
                        <select name="job_definition_id" class="input select mb-4 mt-3" type="text">
                            @foreach($allJobs as $job1)
                                <option value="{{$job1->id}}">{{$job1->title}}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        {{__('Worker')}}
                        <input name="worker" class="input w-full" type="text"
                               placeholder="{{__('Select worker')}}" list="workers-list"/>
                    </label>

                    <label class="input-group flex justify-between mt-3">
                        <div
                            class="self-center justify-self-end">{{__('Start date')}}</div>
                        <input type="date" name="start_date"
                               value="{{old('start_date')??now()->format(\App\DateFormat::HTML_FORMAT)}}"
                               class=" input input-bordered input-primary">
                    </label>

                    <label class="input-group flex justify-between">
                        <div
                            class="self-center justify-self-end">{{__('End date')}}</div>
                        <input type="date" name="end_date"
                               value="{{old('end_date')??now()->addWeeks(3)->format(\App\DateFormat::HTML_FORMAT)}}"
                               class=" input input-secondary input-bordered">
                    </label>

                    <input type="hidden" name="client-0" value="{{\Illuminate\Support\Facades\Auth::user()->id}}"/>

                </form>
                <div class="modal-action">

                    <button class="btn btn-success"
                            onclick="spin('SaveContractButtonMain');document.querySelector('#addContract-Main-form').submit()">
                        <span id="SaveContractButtonMain" class="hidden"></span>
                        {{__('Add')}}
                    </button>

                    <form method="dialog">
                        <!-- if there is a button in form, it will close the modal -->
                        <button class="btn btn-error">{{__('Cancel')}}</button>
                    </form>
                </div>
            </div>
        </dialog>
    </div>


    <datalist id="workers-list">
        @foreach($candidatesForWork as $worker)
            <option value="{{$worker->email}}">{{$worker->firstname}} {{$worker->lastname}}</option>
        @endforeach
    </datalist>

    <table class="table w-full">
        <!-- head -->
        <thead>
            <tr x-show="!$store.empty">
                <x-client-job-list-header/>
            </tr>
            <tr x-show="$store.empty">
                <td class="pl-0">
                    <div class="text-center italic text-xl rounded-lg bg-base-200 w-48">{{__('Nothing to show')}}</div>
                </td>
            </tr>
        </thead>
        <script>
            document.addEventListener('alpine:init', () => {
                {!! $jobs->values()->transform(fn($job) =>"Alpine.store('show$job->id', false)")->join(';')!!}

                {{-- Reset visibility upon saved state... --}}
                setTimeout(() => {
                    updateProjectsVisbility();
                },250);{{-- timeout is to let alpinejs events fire up before taking a decision... --}}
            })

            function updateProjectsVisbility()
            {
                setTimeout(() => {
                    let atLeastOneProjectVisible = false;
                    {!! $jobs->values()->transform(fn($job) =>"atLeastOneProjectVisible |= toggleProjectVisibility('$job->id')")->join(';')!!};
                    Alpine.store('empty',!atLeastOneProjectVisible);
                },250);{{-- timeout is to let alpinejs events fire up before taking a decision... --}}
            }
        </script>

        <tbody>
        @foreach($jobs as $job)

            {{-- JOB DESCRIPTION --}}
            <x-client-job-list-element :job="$job"  />

            {{-- CONTRACTS DETAILS TABLE --}}
            <tr x-show="$store.show{{$job->id}} && $store.show{{$job->id}}main" x-transition.opacity x-data="{massAction:false}">
                <td colspan="6">

                    <table :class="{ 'table-zebra': {!! $showGroupFilterJsEval !!} && !hideAlreadyEvaluated} "
                           class="table table-compact custom-zebra w-full job-{{$job->id}}">
                        <thead>
                        {{-- CONTRACTS MULTI ACTION HEADERS --}}
                        <tr>
                            <th colspan="7">
                                <div class="flex items-center">
                                    <div class="mr-2">
                                        {{__('Action')}}
                                    </div>
                                    <div class="btn-group">

                                        <button type="button" x-bind:disabled="!massAction"
                                                class="btn btn-outline btn-error btn-xs multi-action-{{$job->id}}"
                                                @click="contracts=Array.from(document.getElementsByName('job-{{$job->id}}-contracts[]'))
                                                    .filter(el=>el.checked)
                                                    .map(el=>el.getAttribute('data-workers'));
                                                        setTimeout(()=>document.querySelector('#job-{{$job->id}}-submit').disabled=false,3000)">
                                            <label for="delete-contract-modal-{{$job->id}}">
                                                <i class="fa-solid fa-trash mr-1"></i>{{__('Delete')}}
                                            </label>
                                        </button>

                                        <button x-bind:disabled="!massAction"
                                                class="btn btn-outline btn-warning btn-xs multi-action-{{$job->id}}"
                                                @click="cids=Array.from(document.getElementsByName('job-{{$job->id}}-contracts[]'))
                                                    .filter(el=>el.checked)
                                                    .map(el=>el.getAttribute('value'))
                                                    .join();window.location.href='contracts/bulkEdit/'+cids">
                                            <i class="fa-solid fa-calendar-days mr-1"></i>{{__('Edit')}}
                                        </button>

                                        <button x-bind:disabled="!massAction"
                                                class="btn btn-outline btn-success btn-xs multi-action-{{$job->id}}"
                                                @click="cids=Array.from(document.getElementsByName('job-{{$job->id}}-contracts[]'))
                                                    .filter(el=>el.checked)
                                                    .map(el=>el.getAttribute('value'))
                                                    .join();window.location.href='contracts/evaluate/'+cids">
                                            <i class="fa-solid fa-check mr-1"></i>{{__('Evaluate')}}
                                        </button>
                                    </div>
                                    <div class="m-2">
                                        <button class="btn btn-outline btn-neutral btn-xs"
                                                id="addContractButton{{$job->id}}"
                                                onclick="addContract{{$job->id}}.showModal()">
                                            <i class="fa-solid fa-user-plus"></i>{{__('Add a contract')}}
                                        </button>
                                        <dialog id="addContract{{$job->id}}" class="modal">
                                            <div class="modal-box">
                                                <h3 class="font-bold text-lg">{{__('Add a contract')}}
                                                    ({{Str::words($job->title,5)}})</h3>
                                                <p class="py-4">{{__('Select worker')}}</p>
                                                <form method="post" action="{{route('contracts.store')}}"
                                                      id="addContract-{{$job->id}}-form"
                                                      name="addContract-{{$job->id}}-form">
                                                    @method('POST')
                                                    @csrf

                                                    <label>
                                                        <input name="worker" class="input w-full" type="text"
                                                               placeholder="{{__('Select worker')}}" list="workers-list"/>
                                                    </label>

                                                    <label class="input-group flex justify-between mt-3">
                                                        <div
                                                            class="self-center justify-self-end">{{__('Start date')}}</div>
                                                        <input type="date" name="start_date"
                                                               value="{{old('start_date')??now()->format(\App\DateFormat::HTML_FORMAT)}}"
                                                               class=" input input-bordered input-primary">
                                                    </label>

                                                    <label class="input-group flex justify-between">
                                                        <div
                                                            class="self-center justify-self-end">{{__('End date')}}</div>
                                                        <input type="date" name="end_date"
                                                               value="{{old('end_date')??now()->addWeeks(3)->format(\App\DateFormat::HTML_FORMAT)}}"
                                                               class=" input input-secondary input-bordered">
                                                    </label>

                                                    <input type="hidden" name="client-0"
                                                           value="{{\Illuminate\Support\Facades\Auth::user()->id}}"/>
                                                    <input type="hidden" name="job_definition_id" value="{{$job->id}}"/>

                                                </form>
                                                <div class="modal-action">

                                                    <button class="btn btn-success"
                                                            onclick="spin('SaveContractButton{{$job->id}}');document.querySelector('#addContract-{{$job->id}}-form').submit()">
                                                        <span id="SaveContractButton{{$job->id}}" class="hidden"></span>
                                                        {{__('Add')}}
                                                    </button>

                                                    <form method="dialog">
                                                        <!-- if there is a button in form, it will close the modal -->
                                                        <button class="btn btn-error">{{__('Cancel')}}</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </dialog>
                                    </div>
                                </div>
                            </th>
                        </tr>

                        </thead>
                        <tbody>
                        <form method="post" action="{{route('contracts.destroyAll')}}" id="job-{{$job->id}}-form"
                              x-on:submit.prevent>
                            @method('DELETE')
                            @csrf
                            <input type="hidden" name="job_id" value="{{$job->id}}">
                            @if(app()->environment('testing'))
                                <input class="hidden" type="submit" id="job-{{$job->id}}-form-input-for-test">
                            @endif
                            {{-- CONTRACTS HEADERS --}}
                            <tr>
                                <td>
                                    <label>
                                        <input type="checkbox" class="checkbox" name="contractsForJob{{$job->id}}"
                                               @click="toggleCheckBoxes('job-{{$job->id}}-contracts[]',$event.target.checked);
                                               massAction=isAnyChecked('job-{{$job->id}}-contracts[]')">
                                    </label>
                                </td>

                                <th><i class="fa-solid fa-pager"></i> {{__('Part')}}</th>
                                <th><i class="fa-solid fa-people-roof"></i> {{__('Group')}}</th>
                                <th><i class="fa-solid fa-sack-dollar"></i> {{__('Worker(s)')}}</th>
                                <x-contract-list-header :effort="false"/>
                            </tr>

                            @foreach(auth()->user()->contractsAsAClientForJob($job,$periodId)->get() as $contract)
                                <x-client-contract-list-element :job="$job" :contract="$contract"/>
                            @endforeach
                        </form>
                        </tbody>
                        <tfoot>
                        {{-- EMPTY --}}
                        </tfoot>
                    </table>
                </td>
            </tr>

            <input type="checkbox" id="delete-contract-modal-{{$job->id}}" class="modal-toggle">
            <div class="modal">
                <div class="modal-box">
                    <h3 class="font-bold text-lg">{{__('Do you really want to delete the following contracts ?',)}}</h3>
                    <p class="py-4">

                    <div class="flex flex-wrap">
                        <div class="w-1/4">
                            <i class="fa-solid fa-project-diagram fa-align-center mr-2"></i>
                            <strong> {{__('Project')}}:</strong>
                        </div>
                        <div class="w-3/4">
                            {{$job->title}}
                        </div>
                    </div>
                    </p>

                    <p class="py-4">
                        <i class="fa-solid fa-users-gear"></i> <strong>{{__("Workers")}} (<span
                                x-text="contracts.length"></span>):</strong>
                    <ul class="flex flex-wrap">
                        <template x-for="contract in contracts">
                            <li x-text="contract" class="w-1/3"></li>
                        </template>
                    </ul>
                    </p>


                    <div class="modal-action">
                        <button id="job-{{$job->id}}-submit" disabled
                                @click="document.querySelector('#job-{{$job->id}}-form').submit()"
                                type="button" class="btn btn-outline btn-error">{{__('Yes')}}</button>

                        <label for="delete-contract-modal-{{$job->id}}" class="btn"
                               @click="document.querySelector('#job-{{$job->id}}-submit').disabled=true">{{__('No')}}</label>
                    </div>

                </div>
            </div>

        @endforeach
        </tbody>

        <!-- foot -->
        <tfoot>
        </tfoot>

    </table>

</div>



