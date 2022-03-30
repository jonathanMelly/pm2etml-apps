<x-app-layout>


        <div class="sm:mx-6 flex flex-col gap-4">

            {{-- MY CONTRACTS --}}
            <div class="bg-base-200 overflow-hidden shadow-sm sm:rounded-lg border-secondary border-2 border-opacity-20 hover:border-opacity-30">
                <div class="p-6">
                    <div class="prose pb-2 -p-6">
                        <h1 class="text-base-content">{{__('My contracts')}}</h1>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        @forelse ($contracts as  $contract)
                            <x-job-definition-card :job="$contract->jobDefinition" />
                        @empty
                            <p>{{ __('No contracts') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- TOOLS --}}
            <div class="bg-base-200 overflow-hidden shadow-sm sm:rounded-lg border-neutral border-2 border-opacity-20 hover:border-opacity-30">
                <div class="p-6">
                    <div class="prose pb-2 -p-6">
                        <h1 class="text-base-content">{{__('Internal tools')}}</h1>
                    </div>
                    <x-tools />
                </div>
            </div>

            {{-- JOKE--}}
            <div class="bg-gradient-to-r from-secondary/50 to-base-100 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="prose">
                        <h1 class="text-secondary-content">{{__('A bit of fun')}}</h1>
                    </div>
                    <x-joke />
                </div>
            </div>
        </div>

</x-app-layout>
