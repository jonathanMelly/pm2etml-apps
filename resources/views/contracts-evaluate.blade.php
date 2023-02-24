{{--
 After some trials, to avoid manually handling json data (with old(...) feature), checkboxes have been 'duplicated'
 with hidden fields so the data, even if false, is still kept...
 The best would be to have a 2 options (false/true) radio button with toggle UI...
 --}}
<x-app-layout>
    @push('custom-scripts')
        <script>
            let contractsEvaluations = {};

            function toggle(id, checked = null) {
                //let success = hiddenInput.value;
                let toggleCheckBox = document.querySelector("[name='toggle-" + id + "']");
                let hidden = document.querySelector("[name='success-" + id + "']");

                //Copy from hidden (onload) OR get from function parameters (onchange)
                let success = checked ?? hidden.value === 'true';

                toggleCheckBox.classList.remove('bg-' + (success ? 'error' : 'success'));
                toggleCheckBox.classList.add('bg-' + (!success ? 'error' : 'success'));

                hidden.value = success;
                //Apply value from hidden field (which contains correct old value)
                if (checked == null) {
                    toggleCheckBox.checked = success;
                }

            }

            document.addEventListener("DOMContentLoaded", function () {
                document.querySelectorAll('[type=checkbox]').forEach(el => toggle(el.value));
            });

        </script>
    @endpush
    <form id="eval" x-on:submit.prevent action="{{route('contracts.evaluate')}}" method="post">
        @csrf
        <input type="hidden" id="contractsEvaluations" name="contractsEvaluations" value="">
        <div class="sm:mx-6 bg-base-200 bg-opacity-50 rounded-box sm:p-3 p-1 flex flex-col items-center">

            <div class="stats shadow mb-4">

                <div class="stat">
                    <div class="stat-figure text-secondary">
                        <div class="avatar">
                            <div class="w-24 rounded">
                                <img src="{{route('dmz-asset',['file'=>$job->image?->storage_path])}}" />
                            </div>
                        </div>
                    </div>
                    <div class="stat-title">
                        <i class="fa-solid fa-calendar-day"></i> {{\Illuminate\Support\Carbon::parse($contracts->min('start'))->format(\App\SwissFrenchDateFormat::DATE)}}
                        <i class="fa-solid fa-arrow-right"></i>
                        <i class="fa-solid fa-calendar-days"></i> {{\Illuminate\Support\Carbon::parse($contracts->max('end'))->format(\App\SwissFrenchDateFormat::DATE)}}
                    </div>
                    <div class="stat-value">{{$job->title}}</div>
                    <div class="stat-desc">{{trans_choice(":number evaluation|:number evaluations",sizeof($contracts),['number'=>sizeof($contracts)])}}</div>
                </div>

            </div>

            <table class="table table-compact table-zebra w-auto">
                <thead>
                {{-- CONTRACTS MULTI ACTION HEADERS --}}
                <tr>
                    <th>
                        {{__('Worker(s)')}}
                    </th>
                    <th class="w-96 text-center">{{__('Gave satisfaction')}}</th>
                    <th>{{__('Last evaluated')}}</th>
                </tr>
                </thead>
                <tbody>
                {{-- For historical reasons, contract ids are used ... thus needs 2 imbricated loops --}}
                @foreach($contracts as $contract)
                    @foreach($contract->workersContracts as $workerContract)
                        @php
                            /* @var $contract \App\Models\Contract */
                            /* @var $workerContract \App\Models\WorkerContract */

                            $commentName = 'comment-'.$workerContract->id;
                            $successName = 'success-'.$workerContract->id;

                            //By default, contracts are validated (less work for teacher)
                            $checked = old($successName,$workerContract->alreadyEvaluated()?$workerContract->success:true);


                        @endphp
                        <tr class="h-16">
                            <td class="">{{$workerContract->groupMember->user->getFirstnameL()}}</td>
                            <td class="text-center" x-data="{checked:{{b2s($checked)}} }" class="w-64">
                                <input type="hidden" name="workersContracts[]" value="{{$workerContract->id}}">
                                <input type="hidden" name="{{$successName}}" value="{{b2s($checked)}}">
                                <input type="checkbox" class="toggle"
                                       @click="checked=!checked;toggle({{$workerContract->id}},checked)"
                                       name="toggle-{{$workerContract->id}}" value="{{$workerContract->id}}">

                                <textarea placeholder="{{__('What must be improved')}}..."
                                          class="textarea h-10 pl-1 border-error border text-xs @error($commentName) border-2 border-dashed @enderror"
                                          name="{{$commentName}}"
                                          x-show="!checked">{{old($commentName,$workerContract->success_comment)}}</textarea>
                                @error($commentName)
                                <br/><i class="text-xs text-error">{{$errors->first($commentName)}}</i>
                                @enderror
                            </td>
                            <td class="text-center">
                                {{$workerContract->alreadyEvaluated()?
                                    $workerContract->success_date->format(\App\SwissFrenchDateFormat::DATE_TIME)
                                    :__('-')}}</td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="3"/>
                </tr>
                </tfoot>
            </table>

            <button type="button" class="btn my-2"
                    onclick="document.querySelector('#eval').submit()">
                {{__('Save evaluation results')}}</button>
        </div>


    </form>

</x-app-layout>
