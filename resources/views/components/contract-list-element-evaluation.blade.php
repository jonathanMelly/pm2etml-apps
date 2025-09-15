
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
        @role(\App\Constants\RoleName::STUDENT)
        @if($workerContract->canRemediate())
            <button class="ml-2 btn btn-outline btn-xs btn-success text-xs" onclick="switchClient{{$workerContract->id}}.showModal()">
                <i class="fa-solid fa-wrench fa-xs"></i> {{__('Ask for remediation')}}
            </button>
        @endif
        @endrole


    @endforeach
    <div class="ml-1">
        @if( $multiple)
            <i class="fa-solid fa-people-group"></i>
        @endif
    </div>
</td>
