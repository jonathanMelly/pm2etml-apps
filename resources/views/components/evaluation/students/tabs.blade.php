@props([
    'studentsDatas' => [],
])

<div class="tabs-lifted flex  overflow-x-auto py-2">
    @foreach ($studentsDatas as $index => $student)
        <x-evaluation.students.tab :student="$student" :active="$loop->first" />
    @endforeach
</div>
