<div class="category-container bg-gray-200 rounded-md relative inline-flex flex-col shadow-sm"
     id="{{ $containerId }}">
    
    <!-- Titre de la catégorie -->
    <div class="flex items-center justify-between p-2 border-b border-gray-300">
        <h2 class="text-lg font-semibold text-cyan-700 tracking-wide">
            {{ __($categoryName) }}
        </h2>
    </div>

    <!-- Conteneur des critères -->
    <div class="criteria-flex flex flex-wrap justify-start gap-3 p-2"
         style="display: {{ $isVisible ? 'flex' : 'none' }};">
         
        @foreach ($criterions as $criterion)
            <div class="relative group flex-1 min-w-[275px] max-w-[275px]">
                <!-- Tooltip pour la description -->
                <span
                    class="hidden absolute z-10 bg-gray-700 text-sm text-white px-2 py-1 rounded-md shadow-lg
                           -top-14 left-3 min-w-[250px] group-hover:inline-block transition-opacity duration-200">
                    {{ $criterion['description'] }}
                </span>

                <!-- Carte de critère -->
                <x-evaluation.criteria.card
                    :criterion="$criterion"
                    :visible-sliders="$visibleSliders"
                    :appreciation-labels="$appreciationLabels"
                    :is-teacher="$isTeacher"
                    :evaluation-levels="$evaluationLevels"
                    :id-student="$idStudent"
                    class="criterion-card h-full rounded-lg bg-white shadow-sm transition-transform hover:scale-[1.02]" />
            </div>
        @endforeach
    </div>
</div>
