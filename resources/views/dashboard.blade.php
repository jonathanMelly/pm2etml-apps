<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 gap-4">

            <div class="bg-gradient-to-r from-warning to-secondary overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="prose">
                        <h1 class="text-primary-content">Humour</h1>
                    </div>
                    <x-joke />
                </div>
            </div>

            <div class="bg-base-200 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="prose pb-2">
                        <h1 class="text-base-content">Outils</h1>
                    </div>
                    <x-tools />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
