@if ($isTeacher)
    <span>
        <!-- Bouton pour modifier les critères personnalisés -->
        <a href="{{ $route }}"
            class="flex items-center absolute z-10 top-11 right-20 text-gray-600 hover:text-cyan-400">
            <svg class="w-6 h-6 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9"></path>
                <path
                    d="M16.5 3a2.121 2.121 0 0 0-3 0L4.64 11.36a1 1 0 0 0-.29.71v3.59a1 1 0 0 0 1 1h3.59a1 1 0 0 0 .71-.29L21 7.5a2.121 2.121 0 0 0-3-3l-1.5 1.5">
                </path>
            </svg>
            {{ __($label) }}
        </a>
    </span>
@endif
