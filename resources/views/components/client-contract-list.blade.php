<div>
    <div class="overflow-x-auto w-full">
        <table class="table w-full">
            <!-- head -->
            <thead>
                <tr>
                    <x-client-contract-list-header />
                </tr>
            </thead>

            <tbody>
            @foreach($contracts as $contract)
                <x-client-contract-list-element :contract="$contract" />
            @endforeach
            </tbody>

            <!-- foot -->
            <tfoot>
                <tr>
                    <x-client-contract-list-header />
                </tr>
            </tfoot>

        </table>
    </div>

</div>
