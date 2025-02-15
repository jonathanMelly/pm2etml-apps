<div class="category-container bg-gray-200 rounded-md relative inline-flex flex-col" id="{{ $containerId }}">
    <!-- Titre de la catégorie avec bouton de déroulement -->
    <div class="flex items-center justify-between p-1">
        <h2 class="text-lg font-semibold text-cyan-700">
            {{ __($categoryName) }}
        </h2>
    </div>

    <div class="criteria-flex" style="display: {{ $isVisible ? 'flex' : 'none' }};">
        @foreach ($criterions as $criterion)
            <div class="relative group  px-2 pb-2">
                <!-- Tooltip pour la description -->
                <span
                    class="hidden absolute bg-gray-700 text-xs text-white px-2 py-1 rounded shadow-lg top-[-40px] group-hover:inline-block"
                    id="description-{{ $criterion['id'] }}">
                    {{ $criterion['description'] }}
                </span>
                <!-- Carte de critère -->
                <x-evaluation.criteria.card :criterion="$criterion" :visible-sliders="$visibleSliders" :appreciation-labels="$appreciationLabels" :is-teacher="$isTeacher"
                    :evaluation-levels="$evaluationLevels" :id-student="$idStudent" />
            </div>
        @endforeach
    </div>
</div>
