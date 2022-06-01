
<td class="text-left ">
    <div class="ml-5 w-7 bg-opacity-50 bg-{{$contract->alreadyEvaluated()?($contract->success?'success':'error'):'warning'}}">
        @if($contract->alreadyEvaluated())
            <div class="tooltip" data-tip="{{$contract->success_date}}{{$contract->success?'':' | '.$contract->success_comment}}">
                @endif
                <i class="ml-2 fa-solid fa-{{$contract->alreadyEvaluated()?($contract->success?'square-check':'square-xmark'):'person-digging'}}"></i>
                @if($contract->alreadyEvaluated())
            </div>
        @endif
    </div>
</td>
