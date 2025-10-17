@props([
    'student', 
    'active' => false,
])

<button id="tab-{{ $student->student_id }}-view" role="tab" aria-selected="{{ $active ? 'true' : 'false' }}"
    aria-controls="idStudent-{{ $student->student_id }}"
    class="tab font-bold transition-all duration-200 ease-in-out
        {{ $active ? 'bg-gray-200 text-black border-t-2 border-l-2 border-r-2 border-gray-800 shadow-md' : 'bg-gray-300 hover:bg-gray-400 text-gray-700 hover:text-gray-900' }}
        py-1 px-3 rounded-tl-xl rounded-tr-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50
        hover:scale-105 hover:shadow-lg focus:shadow-xl"
    data-student-id="{{ $student->student_id }}" data-action="toggle-visibility" tabindex="0">
    {{ $student->student_lastname }}
</button>
