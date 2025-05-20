@props([
    'studentId' => null,
    'stateMachine' => null,
    'isTeacher' => false,
    'grade' => 'A',
    'score' => 80,
    'evaluationType' => 'Formative',
    'resultType' => 'saved', // 'saved' pour le résultat enregistré, 'live' pour le résultat en temps réel
])

<div id="id-{{ $studentId }}-finalResult-{{ $resultType }}"
    class="relative bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden h-full min-w-36"
    style="{{ $resultType === 'saved' ? 'border: 2px solid #4caf50;' : 'border: 2px solid #2196f3;' }}">

    {{-- Titre Result Type (design conservé) --}}
    <div class="bg-gray-100 dark:bg-gray-700 text-center py-2 font-medium text-sm text-gray-700 dark:text-gray-300">
        {{ $resultType === 'saved' ? __('Saved Result') : __('Live Result') }}
    </div>

    {{-- Contenu principal (compact et centré) --}}
    <div class="flex flex-col justify-center items-center p-4">
        {{-- Evaluation Type + Grade (centrés) --}}
        <div class="flex flex-col items-center">
            <h6 id="finalResultTitle-{{ $studentId }}-{{ $resultType }}"
                class="font-semibold text-lg text-gray-800 dark:text-gray-200">
                {{ __($evaluationType) }}
            </h6>
            <p id="finalResultContent-{{ $studentId }}-{{ $resultType }}"
                class="text-4xl font-extrabold text-gray-700 dark:text-gray-200">
                {{ $grade }}
            </p>
        </div>

        {{-- Score (en bas à droite) --}}
        <span id="spanResult-{{ $studentId }}-{{ $resultType }}"
            class="absolute right-1 bottom-1 bg-indigo-200 text-white px-3 py-1 rounded-full text-sm font-medium">
            {{ $score }}%
        </span>
    </div>
</div>
