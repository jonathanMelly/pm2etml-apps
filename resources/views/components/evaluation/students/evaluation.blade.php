@php
    $jsonStudent = collect($jsonSave)->first(fn($student) => $student['student_Id'] === $studentDetails->student_id);
    $isUpdate = isset($jsonStudent['evaluations']) && !empty($jsonStudent['evaluations']);
@endphp

<div id="idStudent-{{ $studentDetails->student_id }}-visible"
    style="{{ !$isFirst ? 'display:none;' : 'display:block;' }}">

    <x-evaluation.students.info :isTeacher="$isTeacher" :studentDetails="$studentDetails" />


    <x-evaluation.students.feedback :studentId="$studentDetails->student_id" />

    <form method="post" action="{{ route('evaluation.storeEvaluation') }}" class="space-y-2">
        @csrf
        <x-evaluation.tabs :studentId="$studentDetails->student_id" :hasEval="$studentDetails->stateMachine" />

        <div id="id-{{ $studentDetails->student_id }}-criterias" class="space-y-3">
            @foreach ($criteriaGrouped as $category => $criterions)
                <x-evaluation.criteria.section :container-id="'id-' . $studentDetails->student_id . '-' . strtolower($category) . '-container'" :category-name="$category" :criterions="$criterions" :visible-sliders="$visibleSliders"
                    :appreciation-labels="$appreciationLabels" :is-teacher="$isTeacher" :evaluation-levels="$evaluationLevels" :id-student="$studentDetails->student_id" :is-visible="$visibleCategories[$category]" />
            @endforeach
        </div>

        <x-evaluation.criteria.remark :studentDetails="$studentDetails" :isTeacher="$isTeacher" />

        <input type="hidden" name="evaluation_data" id="evaluation-data-{{ $studentDetails->student_id }}">
        <input type="hidden" name="isUpdate" value="{{ $isUpdate ? 'true' : 'false' }}">

        <div class="flex justify-end">
            <button type="submit" id="id-{{ $studentDetails->student_id }}-buttonSubmit"
                class="w-36 p-2 rounded {{ $isUpdate ? 'bg-orange-500 hover:bg-orange-600' : 'bg-purple-500 hover:bg-purple-600' }} font-semibold text-gray-100"
                data-student-id="{{ $studentDetails->student_id }}" data-update="{{ $isUpdate ? true : false }}">
                {{ $isUpdate ? __('Update evaluation') : __('Submit evaluation') }}
            </button>
        </div>
    </form>
</div>
