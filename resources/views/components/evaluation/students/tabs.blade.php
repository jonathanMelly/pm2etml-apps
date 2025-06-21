@props([
    'studentsDatas' => [],
])

<div class="tabs-lifted flex flex-row overflow-x-auto overflow-y-hidden  custom-scrollbar relative">
    @if (count($studentsDatas) > 1)
        <span class="absolute right-40 top-1/2 transform -translate-y-1/2 text-2xl text-gray-800">...</span>

        @foreach ($studentsDatas as $index => $student)
            <x-evaluation.students.tab :student="$student" :active="$loop->first" />
        @endforeach
    @endif
</div>
