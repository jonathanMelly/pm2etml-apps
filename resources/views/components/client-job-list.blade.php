
    <div class="overflow-x-auto w-full">
        <table class="table w-full">
            <!-- head -->
            <thead>
                <tr>
                    <x-client-job-list-header />
                </tr>
            </thead>

            <tbody x-data="{ show{{$jobs->first()->id}}:true,{{$jobs->values()->skip(1)->transform(fn($job,$key) =>'show'.$job->id.':false,')}} }">
            @foreach($jobs as $job)
                {{-- JOB DESCRIPTION --}}
                <x-client-job-list-element :job="$job" />

                {{-- CONTRACTS DETAILS TABLE --}}
                <tr x-show="show{{$job->id}}" x-transition.opacity>
                    <td colspan="5">
                        <table class="table table-compact table-zebra w-full">
                            <!-- head -->
                            <thead>
                            <tr>
                                <td>
                                    <label>
                                        <input type="checkbox" class="checkbox" name="contractsForJob{{$job->id}}">
                                    </label>
                                </td>
                                <th><i class="fa-solid fa-people-roof"></i> {{__('Group')}}</th>
                                <th><i class="fa-solid fa-sack-dollar"></i> {{__('Worker(s)')}}</th>
                                <x-contract-list-header />
                            </tr>
                            </thead>

                            <tbody>
                            @foreach(auth()->user()->contractsAsAClientForJob($job)->get() as $contract)
                                <x-client-contract-list-element :job="$job" :contract="$contract" />
                            @endforeach
                            </tbody>
                            <tfoot>
                            {{-- EMPTY --}}
                            </tfoot>
                        </table>
                    </td>
                </tr>
            @endforeach
            </tbody>

            <!-- foot -->
            <tfoot>
            </tfoot>

        </table>
    </div>


