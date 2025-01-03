@php
    $multiple = $contract->workers_count > 1;
@endphp
<td class="text-left ">

    @foreach ($contract->workersContracts as $workerContract)
        <div
            class="ml-5 w-7 bg-opacity-50 bg-{{ $workerContract->alreadyEvaluated() ? ($workerContract->success ? 'success' : 'error') : 'warning' }}">
            @if ($workerContract->alreadyEvaluated())
                <div class="tooltip"
                    data-tip="{{ $multiple ? $workerContract->groupMember->user->getFirstnameL() . ':' : '' }}{{ $workerContract->success_date }}{{ $workerContract->success ? '' : ' | ' . $workerContract->success_comment }}">
            @endif
            <i
                class="ml-2 fa-solid fa-{{ $workerContract->alreadyEvaluated() ? ($workerContract->success ? 'square-check' : 'square-xmark') : 'person-digging' }}"></i>
            @if ($workerContract->alreadyEvaluated())
        </div>
    @elseif(isset($job))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Alpine.store('show{{ $job->id }}', true)
            });
        </script>
    @endif

    </div>
    @endforeach
    <div class="ml-1">
        @if ($multiple)
            <i class="fa-solid fa-people-group"></i>
        @endif
    </div>
</td>
