@props([
    'studentsDatas' => [],
])

<div class="tabs-lifted flex flex-row overflow-x-auto overflow-y-hidden  custom-scrollbar relative">
    @if (count($studentsDatas) > 5)
        <!-- Exemple : Afficher l'indicateur si plus de 5 étudiants -->
        <span class="absolute right-0 top-1/2 transform -translate-y-1/2 text-sm text-gray-500">...</span>
    @endif
    @foreach ($studentsDatas as $index => $student)
        <x-evaluation.students.tab :student="$student" :active="$loop->first" />
    @endforeach
</div>
