<div
    class="criterion-card shadow-sm dark:shadow-sm bg-white space-y-4
    dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3
     hover:shadow-lg transition-shadow duration-300 max-w-sm">

    <!-- Nom du critère -->
    <div data-criterion-name="Name"
        class="criterion-name text-xl font-bold flex-1 text-gray-800 dark:text-white mb-4 text-center overflow-hidden whitespace-nowrap overflow-ellipsis max-w-lg">
        {{ $criterion['name'] }}
    </div>

    <!-- Conteneur des curseurs -->
    <div class="slider-container space-y-2 border-t border-gray-300 dark:border-gray-600 pt-4">
        <!-- Curseur Auto 80% -->
        <div class="flex space-x-2 items-center" id="id-{{ $idStudent }}-auto80-{{ $criterion['position'] }}">
            <label for="{{ $idStudent }}-range-auto80-{{ $criterion['position'] }}"
                class="w-[80px] text-sm text-gray-600 dark:text-gray-300 font-medium mb-1 whitespace-nowrap">
                {{ __('A-eval 3/4') }}
            </label>
            <input type="range" min="0" max="3" @if (!$isTeacher) value="2" @endif
                class="range range-primary range-sm w-full disabled:cursor-not-allowed"
                data-student-id="{{ $idStudent }}" data-criterion-id="{{ $criterion['position'] }}"
                data-level="auto80" aria-label="{{ __('A-eval 3/4') }}"
                id="id-{{ $idStudent }}-range-auto80-{{ $criterion['position'] }}"
                value="{{ $sliderValues['auto80'][$criterion['position']] ?? 0 }}" oninput="updateSliderValue(this)"
                disabled>
            <div id="id-{{ $idStudent }}-result-auto80-{{ $criterion['position'] }}"
                class="w-12 text-sm text-gray-500 dark:text-gray-300 ml-2">
                {{ $appreciationLabels[$sliderValues['auto80'][$criterion['position']] ?? 0] }}
            </div>
        </div>

        <!-- Curseur Eval 80% -->
        <div class="flex space-x-2 items-center" id="id-{{ $idStudent }}-eval80-{{ $criterion['position'] }}">
            <label for="range-eval80-{{ $criterion['position'] }}"
                class="w-[80px] text-sm text-gray-600 dark:text-gray-300 font-medium mb-1 whitespace-nowrap">
                {{ __('Eval 3/4') }}
            </label>
            <input type="range" min="0" max="3" @if ($isTeacher) value="2" @endif
                class="range range-secondary range-sm w-full disabled:cursor-not-allowed"
                aria-label="{{ __('Eval 3/4') }}"
                id="id-{{ $idStudent }}-range-eval80-{{ $criterion['position'] }}"
                data-student-id="{{ $idStudent }}" data-criterion-id="{{ $criterion['position'] }}"
                data-level="eval80" value="{{ $sliderValues['eval80'][$criterion['position']] ?? 0 }}"
                oninput="updateSliderValue(this)" disabled>
            <div id="id-{{ $idStudent }}-result-eval80-{{ $criterion['position'] }}"
                class="w-12 text-sm text-gray-500 dark:text-gray-300 ml-2">
                {{ $appreciationLabels[$sliderValues['eval80'][$criterion['position']] ?? 0] }}
            </div>
        </div>

        <!-- Curseurs selon le rôle (Auto et Eval 100%) -->
        @foreach (['auto100', 'eval100'] as $type)
            <div class="flex space-x-2 items-center"
                id="id-{{ $idStudent }}-{{ $type }}-{{ $criterion['position'] }}"
                style="{{ $type === 'auto100' || (!$isTeacher && $type === 'eval100') ? 'display:none' : '' }}">
                <label for="{{ $idStudent }}-range-{{ $type }}-{{ $criterion['position'] }}"
                    class="w-[80px] text-sm text-gray-600 dark:text-gray-300 font-medium mb-1 whitespace-nowrap">
                    {{ ucfirst(__($type === 'auto100' ? 'A-eval 100%' : 'Eval 100%')) }}
                </label>
                <input type="range" min="0" max="3"
                    class="range range-sm {{ $type === 'auto100' ? 'range-primary' : 'range-secondary' }} w-full disabled:cursor-not-allowed"
                    aria-label="{{ ucfirst($type) }}" data-student-id="{{ $idStudent }}"
                    data-criterion-id="{{ $criterion['position'] }}" data-level="{{ $type }}"
                    id="id-{{ $idStudent }}-range-{{ $type }}-{{ $criterion['position'] }}"
                    value="{{ $sliderValues[$type][$criterion['position']] ?? 0 }}" oninput="updateSliderValue(this)"
                    {{ $type === 'auto100' || (!$isTeacher && $type === 'eval100') ? 'disabled' : '' }}>
                <div id="id-{{ $idStudent }}-result-{{ $type }}-{{ $criterion['position'] }}"
                    class="w-12 text-sm text-gray-500 dark:text-gray-300 ml-2">
                    {{ $appreciationLabels[$sliderValues[$type][$criterion['position']] ?? 0] }}
                </div>
            </div>
        @endforeach
    </div>

    <!-- Options et remarques -->
    <div class="options-and-remarks mt-3 space-y-2">
        <!-- Case d'exclusion -->
        <div class=" absolute top-1 left-6 exclude-checkbox flex items-center space-x-3 my-3 cursor-pointer">
            <!-- Checkbox personnalisée -->
            <label class="swap swap-rotate" onclick="toggleExclusion(this)">
                <!-- Icône "vu" (coche) -->
                <input type="checkbox" class="swap-input hidden" data-exclude-id="{{ $criterion['position'] }}"
                    id="id-{{ $idStudent }}-exclude-{{ $criterion['position'] }}"
                    data-student-id="{{ $idStudent }}" {{ $criterion['position'] === 8 ? 'checked' : '' }} />

                <!-- État "non coché" (vu/coche) -->
                <svg class="swap-off fill-current text-green-500 w-6 h-6" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24">
                    <path
                        d="M10,17L5,12L6.41,10.59L10,14.17L17.59,6.58L19,8M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3Z" />
                </svg>

                <!-- État "coché" (croix) -->
                <svg class="swap-on fill-current text-red-500 w-6 h-6" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24">
                    <path
                        d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" />
                </svg>
            </label>

            <!-- Label associé
             <label for="id-{{ $idStudent }}-exclude-{{ $criterion['position'] }}"
                class="label-text text-gray-900 dark:text-gray-200 font-medium">{{ __('Exclude from evaluation') }}</label>
                -->

        </div>

        <!-- Zone de remarque -->
        <div class="remark space-y-1">
            <label for="{{ $idStudent }}-remark-{{ $criterion['position'] }}"
                class="block mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('Remark') }}
            </label>
            <textarea data-student-id="{{ $idStudent }}" data-textarea-id="{{ $criterion['position'] }}"
                id="id-{{ $idStudent }}-remark-{{ $criterion['position'] }}"
                name="remark[{{ $idStudent }}-{{ $criterion['position'] }}]"
                class="w-full rounded focus:ring-2 focus:ring-indigo-500 focus:outline-none resize-none
           text-gray-900 dark:text-gray-200 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600
           hover:border-gray-400 dark:hover:border-gray-500 p-2">
           </textarea>
        </div>
    </div>
</div>
