<style>
    .table-auto {
        table-layout: auto;
    }

    th {
        background-color: #eee;
        color: black;
        font-weight: bold;
        font-size: large;
        white-space: normal;
        word-wrap: break-word;
        word-break: break-word;
    }

    td {
        text-align: center;
    }

    .legend {
        color: #aaa;
        font-weight: normal;
        font-size: small;
    }
</style>
<x-app-layout>
    <div class="d-flex flex-row justify-content-center">
        <table class="table table-bordered w-auto mx-auto">
            <thead>
                <tr>
                    <th class="legend">{{ __('Showing') }}:<br>client<br>{{ __('priority') }}</th>
                    @foreach($jobTitles as $job)
                    <th>{{ $job }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($applicants as $applicant)
                <tr>
                    <th>{{ $applicant }}</th>
                    @foreach($jobTitles as $job)
                    <td>
                        {{ isset($matrix[$applicant][$job]) ? $matrix[$applicant][$job]->contract->clients->first()->firstname : "" }}
                        <br>
                        {{ isset($matrix[$applicant][$job]) ? $matrix[$applicant][$job]->application_status : "" }}
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>