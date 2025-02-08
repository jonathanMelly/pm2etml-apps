<div class="category-container bg-gray-200 rounded-md relative inline-block mr-auto" style="order: {{ $order }};"
    id="{{ $containerId }}">
    <!-- Titre de la catégorie avec bouton de déroulement -->
    <div class="flex items-center justify-between p-1">
        <h2 class="text-lg font-semibold text-cyan-700">
            {{ __($categoryName) }}
        </h2>
        <button type="button" class="btn btn-sm bg-gray-500 text-white px-2 py-1 rounded"
            onclick="toggleVisibility('{{ $containerId }}')">
            {{ $isVisible ? '▲' : '▼' }}
        </button>
    </div>

    <div class="criteria-flex" style="display: {{ $isVisible ? 'flex' : 'none' }};">
        @foreach ($criterions as $criterion)
            <div class="relative group">
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
