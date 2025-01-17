<div class="remark mt-4 relative w-full">
    <label for="generalRemark" class="w-full block font-medium text-gray-900 dark:text-gray-200 mb-2">
        {{ __('General remark') }}
    </label>

    <div class="flex h-44 relative">
        <div class="flex w-full  mx-3 space-y-2 rounded-md border border-gray-300 dark:border-gray-600
            bg-gray-50 dark:bg-gray-800 dark:text-gray-200"
            id="generalRemark-area">

            <!-- Zone de texte pour la remarque -->
            <textarea id="id-{{ $studentDetails->student_id }}-generalRemark" name="generalRemark"
                class="textarea textarea-bordered w-full dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 px-4 resize-none"
                oninput="updateTextareaGeneralRemark(this, document.getElementById('charCounter'))"
                onfocus="updateTextareaGeneralRemark(this, document.getElementById('charCounter'))">
            </textarea>

            <span id="charCounter" class="absolute right-56 -bottom-[1.75rem] textarea-info">
                10000/10000
            </span>

            <!-- ToDo List for Teacher -->
            @if ($isTeacher)
                <div id="todo-list-container" class="hidden p-3 my-2 scroll-m-2 overflow-y-auto h-[10rem]">
                    <h6 id='msgTodo'>{{ __('Please fill all sections') }}</h6>
                    <div class="todo-item my-1 space-x-2">
                        <!-- Template for to-do item here -->
                    </div>
                    <!-- Bouton Ajouter -->
                    <button type="button" class="btn btn-primary rounded absolute right-56 -bottom-[3rem]"
                        onclick="addTodoItem(this)">
                        {{ __('Add task') }}
                    </button>
                </div>
            @endif
        </div>

        <!-- Final Result Display -->
        <div id="id-{{ $studentDetails->student_id }}-finalResult" class="relative bg-success">

            @if ($studentDetails->stateMachine)
                @php
                    $text = match ($studentDetails->stateMachine->getCurrentState()->value) {
                        'not_evaluated' => 'Not done.',
                        'auto80' => 'Student',
                        'eval80' => 'Teacher',
                        'auto100' => 'Student',
                        'eval100' => 'Teacher',
                        'pending_signature' => 'Pending sign.',
                        'completed' => 'Done.',
                        default => 'Unknown.',
                    };
                @endphp
                <span id="currentState"
                    class="absolute w-full text-center -top-5 
                bg-white text-sm text-gray-500">
                    {{ __($text) }}
                </span>
            @endif


            <div class="w-full rounded-lg shadow-sm p-6 flex flex-col items-center">
                <span id="id-{{ $studentDetails->student_id }}-status" class="text-center bottom-2">
                    {{-- texte qui indique le status actuel de l'évaluation --}}
                </span>
                <h6 id="finalResultTitle-{{ $studentDetails->student_id }}" class="font-semibold text-xl text-gray-800">
                    {{ __('Formative') }}
                </h6>
                <p id="finalResultContent" class="text-xl font-extrabold text-gray-700 align-middle mt-5 font-serif">
                    A
                </p>
                <span id="spanResult" class="absolute bottom-1 right-1 bg-indigo-300 text-white px-2 py-1 text-sm">
                    80%
                </span>
            </div>
        </div>
    </div>
</div>
