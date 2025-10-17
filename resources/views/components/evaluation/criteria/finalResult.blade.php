<div id="id-{{ $studentId }}-finalResult-{{ $resultType }}"
     class="relative bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden flex flex-col justify-between min-w-40 min-h-[9rem]"
     style="{{ $resultType === 'saved' ? 'border: 2px solid #22c55e;' : 'border: 2px solid #3b82f6;' }}">

    {{-- Bandeau supérieur --}}
    <div class="bg-gray-100 dark:bg-gray-700 text-center py-2 font-medium text-sm text-gray-800 dark:text-gray-200">
        {{ $resultType === 'saved' ? __('Résultat enregistré') : __('Résultat en direct') }}
    </div>

    {{-- Zone principale --}}
    <div class="flex flex-col justify-center items-center flex-grow px-4 py-3 relative min-h-40">
        <div class="flex flex-col items-center text-center space-y-2">
            {{-- Type d’évaluation (modifiable par JS) --}}
            <h6 id="finalResultTitle-{{ $studentId }}-{{ $resultType }}"
                class="font-semibold text-base text-gray-800 dark:text-gray-100 tracking-wide">
                {{ __($evaluationType ?? '—') }}
            </h6>

            {{-- Résultat principal (modifiable par JS) --}}
            <p id="finalResultContent-{{ $studentId }}-{{ $resultType }}"
               class="text-4xl font-extrabold text-gray-800 dark:text-gray-100 leading-tight">
               {{ $grade ?? '—' }}
            </p>
        </div>

        {{-- Score ou étiquette dynamique --}}
        @if (!is_null($score))
            <span id="spanResult-{{ $studentId }}-{{ $resultType }}"
                  class="absolute right-2 bottom-2 bg-indigo-500 text-white px-3 py-1 rounded-md text-sm font-medium shadow-sm">
                {{ $score }}%
            </span>
        @else
            <span id="spanResult-{{ $studentId }}-{{ $resultType }}"
                  class="absolute right-2 bottom-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 px-3 py-1 rounded-md text-sm font-medium shadow-sm">
                —
            </span>
        @endif
    </div>
</div>

{{-- Mini résumé (affiché uniquement à la fin ou sur demande JS) --}}
<div id="id-{{ $studentId }}-small_finalResult"
     class="hidden justify-center items-center gap-2 text-sm px-3 py-2 rounded-lg mt-2 bg-gray-100 dark:bg-gray-700 shadow-sm">
    <span id="smallResultTitle-{{ $studentId }}" class="font-semibold"></span>
    <span id="smallResultContent" class="font-medium text-gray-800 dark:text-gray-200"></span>
</div>
