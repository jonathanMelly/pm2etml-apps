
@php
    $multiple = $contract->workers_count > 1;
    /* @var $workerContract \App\Models\WorkerContract */
@endphp
<td class="text-left flex">
    @foreach($contract->workersContracts as $workerContract)

    <div class="ml-5 w-7 bg-opacity-50 bg-{{$workerContract->alreadyEvaluated()?($workerContract->success?'success':'error'):'warning'}}">
        @if($workerContract->alreadyEvaluated())
            <div class="tooltip" data-tip="{{$multiple?$workerContract->groupMember->user->getFirstnameL().':':''}}{{$workerContract->success_date}}{{$workerContract->success?'':' | '.$workerContract->success_comment}}">
        @endif
                <i class="ml-2 fa-solid fa-{{$workerContract->alreadyEvaluated()?($workerContract->success?'square-check':'square-xmark'):'person-digging'}}"></i>
        @if($workerContract->alreadyEvaluated())
            </div>
        @elseif(isset($job))
            {{-- by default, show not already evaluated jobs --}}
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    Alpine.store('show{{$job->id}}', true)
                });
            </script>
        @endif
    </div>

        {{-- Show attachment icon if evaluation has supporting documents --}}
        @if($workerContract->alreadyEvaluated())
            @php
                $evaluationAttachments = $workerContract->evaluationAttachments;
            @endphp
            @if($evaluationAttachments->isNotEmpty())
                @foreach($evaluationAttachments as $attachment)

                    <a href="{{route('dmz-asset', ['file' => $attachment->storage_path, 'name' => encrypt($attachment->name)])}}"
                       target="_blank"
                       class="ml-2 tooltip"
                       data-tip="{{__('View evaluation document')}} - {{$attachment->name}}">
                        <i class="fa-solid fa-xl fa-file-circle-check text-accent hover:opacity-70 cursor-pointer" title="PDF"></i>
                    </a>

                @endforeach
            @endif
        @endif
        {{-- Pulse Evaluation Indicator & Button --}}
        @php
            // Find if there's an evaluation for this student
            $evaluation = \App\Models\Evaluation::where('student_id', $workerContract->groupMember->user->id)
                ->where('job_definition_id', $contract->jobDefinition->id)
                ->first();

            $pulseStatus = null;
            if ($evaluation) {
                if (auth()->user()->hasRole(\App\Constants\RoleName::STUDENT)) {
                    $pulseStatus = $evaluation->getStudentStatus();
                } elseif (auth()->user()->hasRole(\App\Constants\RoleName::TEACHER)) {
                    $pulseStatus = $evaluation->getTeacherStatus();
                }
            }
        @endphp

        @if($evaluation && $pulseStatus)
            <div class="ml-2 flex items-center" title="Pulse: {{ $pulseStatus }}">
                @if($pulseStatus === 'new')
                    <span class="badge badge-warning badge-xs font-bold animate-pulse">NEW</span>
                @elseif($pulseStatus === 'modified')
                    <i class="fa-solid fa-rotate text-info fa-sm"></i>
                @elseif($pulseStatus === 'viewed')
                    <i class="fa-solid fa-check text-success fa-sm"></i>
                @endif
            </div>
        @endif

        @role(\App\Constants\RoleName::STUDENT)
        @if($workerContract->canRemediate())
            <button class="ml-2 btn btn-outline btn-xs btn-success text-xs" onclick="switchClient{{$workerContract->id}}.showModal()">
                <i class="fa-solid fa-wrench fa-xs"></i> {{__('Ask for remediation')}}
            </button>
        @endif
        
        @if($evaluation)
            <a href="{{ route('eval_pulse.bulk', ['ids' => $workerContract->id]) }}" 
               class="ml-4 btn btn-outline btn-xs btn-primary text-xs">
                <i class="fa-solid fa-heart-pulse fa-xs"></i> {{__('Self Eval')}}
            </a>
        @else
            <a href="{{ route('eval_pulse.bulk', ['ids' => $workerContract->id]) }}" 
               class="ml-4 btn btn-outline btn-xs btn-primary text-xs">
                <i class="fa-solid fa-heart-pulse fa-xs"></i> {{__('Self Eval')}}
            </a>
        @endif
        @endrole

        @role(\App\Constants\RoleName::TEACHER)
            @if($evaluation)
                <a href="{{ route('eval_pulse.bulk', ['ids' => $workerContract->id]) }}" 
                   class="ml-4 btn btn-ghost btn-xs text-primary">
                    <i class="fa-solid fa-heart-pulse"></i>
                </a>
            @endif
        @endrole


    @endforeach
    <div class="ml-1">
        @if( $multiple)
            <i class="fa-solid fa-people-group"></i>
        @endif
    </div>
</td>
