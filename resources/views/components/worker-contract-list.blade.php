
    <div class="overflow-x-auto">
        <table class="table table-compact table-zebra w-full">
            <!-- head -->
            <thead>
                <tr>
                    <x-worker-contract-list-header />
                </tr>
            </thead>

            <tbody>
            @foreach($contracts as $contract)
                <x-worker-contract-list-element :contract="$contract" />
            @endforeach
            </tbody>

            <!-- foot -->
            <tfoot>
                <tr>
                    <x-worker-contract-list-header />
                </tr>
            </tfoot>

        </table>
    </div>


