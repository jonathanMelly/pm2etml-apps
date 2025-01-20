<div class="category-container mb-4 p-3 bg-gray-300 rounded-md relative" id="{{ $containerId }}">
    <h2 class="text-xl font-bold text-cyan-700">
        {{ __($categoryName) }}
    </h2>

    <button type="button" class="btn btn-sm bg-gray-500 text-white p-2 rounded absolute top-3 right-2"
        onclick="toggleVisibility('{{ $containerId }}')">
        {{ $isVisible ? '▲' : '▼' }}
    </button>

    <!-- Grille de critères -->
    <div class="criteria-grid grid gap-2 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
        style="display: {{ $isVisible ? 'grid' : 'none' }};">
        @foreach ($criterions as $criterion)
            <div class="mt-2 relative group">
                <span class="hidden absolute bg-gray-700 text-white p-2 rounded mt-[-50px] shadow-lg"
                    id="description-{{ $criterion['id'] }}">
                    {{ $criterion['description'] }}
                </span>
                <x-criterion-card :criterion="$criterion" :visible-sliders="$visibleSliders" :appreciation-labels="$appreciationLabels" :is-teacher="$isTeacher"
                    :evaluation-levels="$evaluationLevels" :id-student="$idStudent" />
            </div>
        @endforeach
    </div>
</div>
