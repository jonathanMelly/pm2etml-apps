@php
    use App\Constants\AssessmentTiming;
    use Illuminate\Support\Str;

    // Niveaux et libellés centralisés
    $evaluationLevels = AssessmentTiming::all();
    $shortLabels = AssessmentTiming::shortLabels();

    // Déterminer s'il faut masquer le curseur selon le rôle
    $hiddenStyle = fn(string $level) =>
        $level === AssessmentTiming::AUTO_FINALE || (!$isTeacher && $level === AssessmentTiming::EVAL_FINALE)
            ? 'display:none' : '';

    // Couleur : bleu pour auto, violet pour éval
    $rangeClass = fn(string $dom) => Str::startsWith($dom, 'auto') ? 'range-primary' : 'range-secondary';

    // Préparation des curseurs
    $sliders = collect($evaluationLevels)->map(function ($level) use ($shortLabels, $isTeacher, $idStudent, $criterion, $appreciationLabels, $hiddenStyle, $rangeClass) {
        $dom = $level;
        return [
            'dom' => $dom,
            'label' => $shortLabels[$level] ?? $level,
            'value' => 2,
            'hiddenStyle' => $hiddenStyle($level),
            'rangeClass' => $rangeClass($dom),
            'resultLabel' => $appreciationLabels[2] ?? $appreciationLabels[0],
            'id' => "id-{$idStudent}-{$dom}-{$criterion['position']}",
            'rangeId' => "id-{$idStudent}-range-{$dom}-{$criterion['position']}",
            'resultId' => "id-{$idStudent}-result-{$dom}-{$criterion['position']}",
        ];
    });

    $baseId = "id-{$idStudent}-{$criterion['position']}";
@endphp

<div class="criterion-card shadow-sm dark:shadow-sm bg-white space-y-4 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 hover:shadow-lg transition-shadow duration-300 max-w-sm">

    <!-- Nom du critère -->
    <div class="criterion-name text-xl font-bold text-gray-800 dark:text-white mb-4 text-center overflow-hidden whitespace-nowrap max-w-lg"
        data-criterion-name="{{ $criterion['name'] }}">
        {{ Str::limit($criterion['name'], 20, '...') }}
    </div>

    <!-- Curseurs -->
    <div class="slider-container space-y-2 border-t border-gray-300 dark:border-gray-600 pt-4 text-left">
        @foreach ($sliders as $slider)
            <div class="flex space-x-2 items-center" id="{{ $slider['id'] }}" style="{{ $slider['hiddenStyle'] }}">
                <label for="{{ $slider['rangeId'] }}" class="w-[140px] text-sm text-gray-600 dark:text-gray-300 font-medium mb-1 whitespace-nowrap">
                    {{ $slider['label'] }}
                </label>

                <input type="range" min="0" max="3"
                    class="range range-sm {{ $slider['rangeClass'] }} w-full disabled:cursor-not-allowed"
                    aria-label="{{ $slider['label'] }}" data-student-id="{{ $idStudent }}"
                    data-criterion-id="{{ $criterion['position'] }}" data-level="{{ $slider['dom'] }}"
                    id="{{ $slider['rangeId'] }}" value="{{ $slider['value'] }}"
                    oninput="updateSliderValue(this)" disabled>

                <div id="{{ $slider['resultId'] }}" class="w-12 text-sm text-gray-500 dark:text-gray-300 ml-2">
                    {{ $slider['resultLabel'] }}
                </div>
            </div>
        @endforeach
    </div>

    <!-- Options / Remarque -->
    <div class="options-and-remarks mt-3 space-y-2">

        <!-- Exclure du calcul -->
        <div class="absolute top-1 left-6 exclude-checkbox flex items-center space-x-3 my-3 cursor-pointer">
            <label class="swap swap-rotate" onclick="toggleCheckbox(this)">
                <input type="checkbox" class="swap-input hidden" data-exclude-id="{{ $criterion['position'] }}"
                    id="{{ $baseId }}-exclude" data-student-id="{{ $idStudent }}"
                    {{ $criterion['position'] === 8 ? 'checked' : '' }} />

                <svg class="swap-off fill-current text-green-500 w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M10,17L5,12L6.41,10.59L10,14.17L17.59,6.58L19,8M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3Z" />
                </svg>

                <svg class="swap-on fill-current text-red-500 w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" />
                </svg>
            </label>
        </div>

        <!-- Remarque -->
        <div class="remark text-left relative">
            <label for="{{ $baseId }}-remark" class="block mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('Remarque') }}
            </label>

            <label class="swap swap-rotate absolute -top-3 left-12" onclick="toggleRemark(this)">
                <input type="checkbox" class="swap-input hidden" data-remark-id="{{ $criterion['position'] }}"
                    id="{{ $baseId }}-remark-toggle" data-student-id="{{ $idStudent }}" />

                <svg class="swap-off fill-current text-green-500 w-16 h-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M7 10l5 5 5-5z" />
                </svg>

                <svg class="swap-on fill-current text-red-500 w-16 h-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M7 14l5-5 5 5z" />
                </svg>
            </label>

            <textarea data-student-id="{{ $idStudent }}" data-textarea-id="{{ $criterion['position'] }}"
                id="{{ $baseId }}-remark" name="remark[{{ $idStudent }}-{{ $criterion['position'] }}]"
                class="w-full rounded focus:ring-2 focus:ring-indigo-500 focus:outline-none resize-none
                       text-gray-900 dark:text-gray-200 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600
                       hover:border-gray-400 dark:hover:border-gray-500 p-2 hidden"></textarea>
        </div>
    </div>
</div>
