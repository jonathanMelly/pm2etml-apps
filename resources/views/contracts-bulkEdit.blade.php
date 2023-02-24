<x-app-layout>

    @push('custom-scripts')
        <script>
        function updateAll(start,end)
        {
            const elements = {'starts': start, 'ends': end};
            for(let selector in elements)
            {
                document.querySelectorAll("[name^='"+selector+"']").forEach((el)=>{
                    el.value=elements[selector];
                    el.dispatchEvent(new Event('input'));//Needed for alpinejs x-model to update after manual el.value set
                });
            }
        }
        </script>
    @endpush

    <form id="eval" x-on:submit.prevent action="{{route('contracts.bulkUpdate')}}" method="post">
        @csrf
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
                        <i class="fa-solid fa-calendar-day"></i> {{$contracts->min('start')->format(\App\SwissFrenchDateFormat::DATE)}}
                        <i class="fa-solid fa-arrow-right"></i>
                        <i class="fa-solid fa-calendar-days"></i> {{\Illuminate\Support\Carbon::parse($contracts->max('end'))->format(\App\SwissFrenchDateFormat::DATE)}}
                    </div>
                    <div class="stat-value">{{$job->title}}</div>
                    <div class="stat-desc">{{trans_choice(":number selected contract|:number selected contracts",sizeof($contracts),['number'=>sizeof($contracts)])}}</div>
                </div>

            </div>

            <table class="table table-compact table-zebra w-auto">
                <thead>
                {{-- CONTRACTS MULTI ACTION HEADERS --}}
                <tr>
                    <th>
                        {{__('Worker(s)')}}
                    </th>
                    <th class="w-96 text-center">{{__('Start')}}</th>
                    <th class="text-center">{{__('End')}}</th>
                    <th class="text-center">{{__('Action')}}</th>
                </tr>
                </thead>
                <tbody >
                {{-- For historical reasons, contract ids are used ... thus needs 2 imbricated loops --}}
                @php
                    $i=0;
                @endphp
                @foreach($contracts as $contract)
                    @foreach($contract->workersContracts as $workerContract)
                        @php
                        $start = old("starts.$i",$contract->start?->format(\App\DateFormat::HTML_FORMAT));
                        $end = old("ends.$i",$contract->end?->format(\App\DateFormat::HTML_FORMAT));
                        @endphp
                        <tr class="h-16" x-data="{start:'{{$start}}',end:'{{$end}}'}">
                            <td class="">{{$workerContract->groupMember->user->getFirstnameL()}}</td>
                            <td class="text-center">
                                <input type="hidden" name="workersContracts[{{$i}}]" value="{{$workerContract->id}}">
                                <input x-model="start" type="date" name="starts[{{$i}}]" class="input input-bordered input-primary @error("workersContracts.$i") !bg-error @enderror">
                            </td>
                            <td class="text-center">
                                <input x-model="end" type="date" name="ends[{{$i}}]" class="input input-bordered input-secondary @error("workersContracts.$i") !bg-error @enderror">
                            </td>
                            <td class="text-right">
                                <button
                                    type="button"
                                    @click="updateAll(start,end)"
                                    class="btn btn-sm btn-secondary">
                                    {{__('Apply to all')}}
                                </button>
                            </td>
                        </tr>
                        @php
                            $i++;
                        @endphp
                    @endforeach
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="3"/>
                </tr>
                </tfoot>
            </table>

            @if($errors->any())
                <div class="alert alert-error shadow-lg w-auto">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>
                        @foreach($errors->all() as $error)
                            {{$error}}
                        @endforeach
                        </span>
                    </div>
            </div>
            @endif

<button type="button" class="btn my-2"
onclick="document.querySelector('#eval').submit()">
{{__('Save modifications')}}</button>

</div>


</form>

</x-app-layout>
