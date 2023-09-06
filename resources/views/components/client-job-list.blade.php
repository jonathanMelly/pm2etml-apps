
    <div class="overflow-x-auto w-full" x-data="{contracts:''}">
        <table class="table w-full">
            <!-- head -->
            <thead>
            <tr>
                <x-client-job-list-header/>
            </tr>
            </thead>

            <tbody x-data="{ show{{$jobs->first()->id}}:true,{{$jobs->values()->skip(1)->transform(fn($job) =>'show'.$job->id.':false')->join(',')}} }">
            @foreach($jobs as $job)
                <form method="post" action="{{route('contracts.destroyAll')}}" id="job-{{$job->id}}-form" x-on:submit.prevent>
                    @method('DELETE')
                    @csrf
                    <input type="hidden" name="job_id" value="{{$job->id}}">
                {{-- JOB DESCRIPTION --}}
                <x-client-job-list-element :job="$job"/>

                {{-- CONTRACTS DETAILS TABLE --}}
                <tr x-show="show{{$job->id}}" x-transition.opacity x-data="{massAction:false}">
                    <td colspan="6">

                        <table class="table table-compact table-zebra w-full">
                            <thead>
                            {{-- CONTRACTS MULTI ACTION HEADERS --}}
                            <tr>
                                <th colspan="7">
                                    <div class="flex items-center">
                                        <div class="mr-2">
                                            {{__('Selection')}}
                                        </div>
                                        <div class="btn-group">

                                            <button type="button" x-bind:disabled="!massAction" class="btn btn-outline btn-error btn-xs multi-action-{{$job->id}}"
                                                    @click="contracts=Array.from(document.getElementsByName('job-{{$job->id}}-contracts[]'))
                                                    .filter(el=>el.checked)
                                                    .map(el=>el.getAttribute('data-workers'));
                                                        setTimeout(()=>document.querySelector('#job-{{$job->id}}-submit').disabled=false,3000)">
                                                <label for="delete-contract-modal-{{$job->id}}">
                                                    <i class="fa-solid fa-trash mr-1"></i>{{__('Delete')}}
                                                </label>
                                            </button>

                                            <button x-bind:disabled="!massAction" class="btn btn-outline btn-warning btn-xs multi-action-{{$job->id}}"
                                                    @click="cids=Array.from(document.getElementsByName('job-{{$job->id}}-contracts[]'))
                                                    .filter(el=>el.checked)
                                                    .map(el=>el.getAttribute('value'))
                                                    .join();window.location.href='contracts/bulkEdit/'+cids">
                                                <i class="fa-solid fa-calendar-days mr-1"></i>{{__('Edit')}}
                                            </button>

                                            <button x-bind:disabled="!massAction" class="btn btn-outline btn-success btn-xs multi-action-{{$job->id}}"
                                                    @click="cids=Array.from(document.getElementsByName('job-{{$job->id}}-contracts[]'))
                                                    .filter(el=>el.checked)
                                                    .map(el=>el.getAttribute('value'))
                                                    .join();window.location.href='contracts/evaluate/'+cids">
                                                <i class="fa-solid fa-check mr-1"></i>{{__('Evaluate')}}
                                            </button>
                                        </div>
                                    </div>
                                </th>
                            </tr>

                            </thead>
                            <tbody>
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
                                <i class="fa-solid fa-project-diagram fa-align-center mr-2"></i> <strong> {{__('Project')}}:</strong>
                            </div>
                            <div class="w-3/4">
                                {{$job->title}}
                            </div>
                        </div>
                        </p>

                        <p class="py-4">
                            <i class="fa-solid fa-users-gear"></i> <strong>{{__("Workers")}} (<span x-text="contracts.length"></span>):</strong>
                        <ul class="flex flex-wrap">
                            <template x-for="contract in contracts">
                                <li x-text="contract" class="w-1/3"></li>
                            </template>
                        </ul>
                        </p>



                        <div class="modal-action">
                            <button id="job-{{$job->id}}-submit" disabled @click="document.querySelector('#job-{{$job->id}}-form').submit()"
                                    type="button" class="btn btn-outline btn-error" >{{__('Yes')}}</button>

                            <label for="delete-contract-modal-{{$job->id}}" class="btn"
                                   @click="document.querySelector('#job-{{$job->id}}-submit').disabled=true">{{__('No')}}</label>
                        </div>

                    </div>
                </div>
                </form>

            @endforeach
            </tbody>

            <!-- foot -->
            <tfoot>
            </tfoot>

        </table>

    </div>



