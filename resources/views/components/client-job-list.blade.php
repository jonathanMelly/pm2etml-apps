
    <div class="overflow-x-auto w-full">
        <table class="table w-full">
            <!-- head -->
            <thead>
                <tr>
                    <x-client-job-list-header />
                </tr>
            </thead>

            <tbody>
            @foreach($jobs as $job)
                @php
                    $contracts = auth()->user()->contractsAsAClientForJob($job)->get();
                @endphp
                <x-client-job-list-element :job="$job" :contracts="$contracts" />
                <tr>
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
                                <th><i class="fa-solid fa-sack-dollar"></i> {{__('Worker')}}</th>
                                <x-contract-list-header />
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($contracts as $contract)
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


