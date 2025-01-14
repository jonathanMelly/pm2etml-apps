<div>
    @if ($isTeacher)
        <!-- Informations de l'étudiant pour l'enseignant -->
        <h3 class="text-xl font-semibold text-blue-600">
            {{ __('Student') }} :
            <span class="text-gray-800">{{ $studentDetails->student_firstname }} {{ $studentDetails->student_lastname }}
                ({{ $studentDetails->student_id }})</span>
        </h3>
        <h4 class="text-lg font-bold text-gray-700">{{ __('Project name') }}: {{ $studentDetails->project_name }}
            ({{ $studentDetails->job_id }})</h4>
        <h4 class="text-lg font-bold text-gray-700">{{ __('Class name') }}: {{ $studentDetails->class_name }}
            ({{ $studentDetails->class_id }})</h4>
    @else
        <!-- Informations de l'enseignant pour l'étudiant -->
        <h3 class="text-xl font-semibold text-green-700">
            {{ __('Teacher name') }}: {{ $studentDetails->evaluator_firstname }}
            {{ $studentDetails->evaluator_lastname }} ({{ $studentDetails->evaluator_id }})
        </h3>
        <h4 class="text-lg font-bold text-gray-700">{{ __('Project name') }}: {{ $studentDetails->project_name }}
            ({{ $studentDetails->job_id }})</h4>
        <h4 class="text-lg font-bold text-gray-700">{{ __('Class name') }}: {{ $studentDetails->class_name }}
            ({{ $studentDetails->class_id }})</h4>
    @endif
</div>
