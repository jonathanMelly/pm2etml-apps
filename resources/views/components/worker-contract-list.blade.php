    <div @class(['overflow-x-auto'=>!$past]) {{$past?"x-show=showPast":""}}>
        <table class="table table-compact table-zebra w-full">
            <!-- head -->
            <thead>
                <tr>
                    <x-worker-contract-list-header :past="$past" />
                </tr>
            </thead>

            <tbody>
            @foreach($contracts as $contract)
                <x-worker-contract-list-element :contract="$contract" :past="$past" />
            @endforeach
            </tbody>

            <!-- foot -->
            <tfoot>
                <tr>
                    <x-worker-contract-list-header :past="$past" />
                </tr>
            </tfoot>

        </table>
    </div>


