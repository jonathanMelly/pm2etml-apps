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

    /* Style de l'arrière-plan ombragé */
    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        /* Couleur de l'ombrage */
        display: none;
        /* Masqué par défaut */
        z-index: 999;
        /* Pour être au-dessus de tout */
    }

    /* Style du pop-up */
    .popup {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        display: none;
    }

    /* Bouton pour fermer le pop-up */
    .popup button {
        margin-top: 10px;
        padding: 10px 20px;
        background-color: #007BFF;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .popup button:hover {
        background-color: #0056b3;
    }

    #popup {
        text-align: center;
    }

    .emphasize {
        font-size: large;
        font-weight: bold;
        margin: 10px;
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
                    @if(isset($matrix[$applicant][$job]))
                    <td data-application_id="{{ $matrix[$applicant][$job]->id }}" data-application_job="{{ $job }}" data-application_applicant="{{ $applicant }}">
                        {{ $matrix[$applicant][$job]->contract->clients->first()->firstname }}
                        <br>
                        {{ $matrix[$applicant][$job]->application_status }}
                    </td>
                    @else
                    <td></td>
                    @endif
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="overlay" id="overlay"></div>
    <div class="popup" id="popup">
        <p>{{ __('Give the job') }}:</p>
        <p id="spnJobTitle" class="emphasize"></p>
        <p>{{ __('to') }}:</p>
        <p id="spnJobApplicant" class="emphasize"></p>
        <form method="post" action="{{ route('applications.confirm') }}" class="w-auto">
            @csrf
            <input type="hidden" id="inpApplicationId" name="applicationid" />
            <button type="submit" class="mb-5">Confirmer</button>
            <div class="float-right fixed-bottom mt-5 text">
                <small>
                    <label for="keep">
                        {{__('Keep other applications')}}
                    </label>
                    <input id="keep" name="keep" type="checkbox" value="1" />
                </small>
            </div>
        </form>
    </div>

</x-app-layout>
@vite(['resources/js/jobApplication.js'])