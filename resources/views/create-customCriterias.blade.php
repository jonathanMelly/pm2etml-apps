<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h1 class="text-2xl font-bold mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ __('Edit') }}
            </h1>

            {{-- @if (session('success'))
                <div class="alert alert-success mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif --}}

            <form method="POST" action="{{ route('update.custom_criterias') }}"
                class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                @for ($i = 0; $i < 8; $i++)
                    <div class="bg-gray-50 p-3 rounded-md shadow-sm space-y-3">
                        <x-criteria-form :criteria="$criterias[$i] ?? null" :index="$i" />
                    </div>
                @endfor
                <div class="col-span-1 md:col-span-2">
                    <button type="submit" class="btn btn-primary w-full flex items-center justify-center mt-3">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        {{ __('Save changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
