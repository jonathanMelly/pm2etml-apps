<x-app-layout>
    <div class="py-6">

        <div class="sm:mx-6 flex flex-col gap-4">

            {{-- MY JOBS --}}
            <div class="bg-base-200 overflow-hidden shadow-sm sm:rounded-lg border-secondary border-2 border-opacity-50 hover:border-opacity-100">
                <div class="p-6">
                    <div class="prose pb-2 -p-6">
                        <h1 class="text-base-content">{{__('My jobs')}}</h1>
                    </div>

                </div>
            </div>

            {{-- TOOLS --}}
            <div class="bg-base-200 overflow-hidden shadow-sm sm:rounded-lg border-neutral border-2 border-opacity-50 hover:border-opacity-100">
                <div class="p-6">
                    <div class="prose pb-2 -p-6">
                        <h1 class="text-base-content">{{__('Internal tools')}}</h1>
                    </div>
                    <x-tools />
                </div>
            </div>

            {{-- JOKE--}}
            <div class="bg-gradient-to-r from-warning to-secondary overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="prose">
                        <h1 class="text-primary-content">{{__('A bit of fun')}}</h1>
                    </div>
                    <x-joke />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
