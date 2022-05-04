<x-app-layout>
    @push('custom-scripts')
        <script>
            var contractsEvaluations = {};
            function toggle(element)
            {
                element.classList.remove('bg-'+(element.checked?'error':'success'));
                element.classList.add('bg-'+(!element.checked?'error':'success'));

                contractsEvaluations[element.value]=element.checked;

                //I tried to do it only on submit but it didn’t seem to work :-(
                document.querySelector('#contractsEvaluations').value=JSON.stringify(contractsEvaluations)
            }

            document.addEventListener("DOMContentLoaded", function() {
                document.querySelectorAll('[type=checkbox]').forEach(el=>toggle(el));
            });

        </script>
    @endpush
    <form id="eval" x-on:submit.prevent action="{{route('contracts.evaluate')}}" method="post">
        @csrf
        <input type="hidden" id="contractsEvaluations" name="contractsEvaluations" value="">
        <div class="sm:mx-6 bg-base-200 bg-opacity-50 rounded-box sm:p-3 p-1 flex flex-col items-center">

            <table class="table table-compact table-zebra w-auto">
                <thead>
                {{-- CONTRACTS MULTI ACTION HEADERS --}}
                <tr>
                    <th>
                        {{__('Worker(s)')}}
                    </th>
                    <th>{{__('Gave satisfaction')}}</th>
                    <th>{{__('Last evaluated')}}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($contracts as $contract)
                <tr>
                    <td class="">{{$contract->workers->transform(fn($el)=>$el->user->getFirstnameL())->join(',')}}</td>
                    <td class="text-center">
                        <input type="checkbox" class="toggle" onchange="toggle(this)"
                               @checked($contract->alreadyEvaluated()?$contract->success:true)
                               name="contracts[]" value="{{$contract->id}}">
                    </td>
                    <td class="text-center">
                        {{$contract->alreadyEvaluated()?
                            $contract->success_date->format(\App\SwissFrenchDateFormat::DATE_TIME)
                            :__('-')}}</td>
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="3" />
                </tr>
                </tfoot>
            </table>

            <button type="button" class="btn my-2"
                    onclick="document.querySelector('#eval').submit()">
                {{__('Save evaluation results')}}</button>
        </div>



    </form>

</x-app-layout>
