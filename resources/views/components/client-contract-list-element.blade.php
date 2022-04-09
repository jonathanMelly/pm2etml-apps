<tr>
    <th>
        <label>
            <input name="contracts[]" type="checkbox" class="checkbox">
        </label>
    </th>
    <td>
        <div class="flex items-center space-x-3">
            <div class="avatar">
                <div class="mask mask-squircle w-12 h-12">
                    <img src="{{img($contract->jobDefinition->image)}}" alt="{{$contract->jobDefinition->name}}" />
                </div>
            </div>
            <div>
                <div class="font-bold">{{$contract->jobDefinition->name}}</div>
                <div class="text-sm opacity-50">
                    {{$contract->status->name}}
                    <ul class="steps">
                        <li class="step step-primary">Register</li>
                        <li class="step step-primary">Choose plan</li>
                        <li class="step">Purchase</li>
                        <li class="step">Receive Product</li>
                    </ul>
                </div>
            </div>
        </div>
    </td>
    <td>
        {{$contract->end_date}}
        <br>
        <span class="badge badge-ghost badge-sm">{{$contract->start_date}}</span>
    </td>
    <td><i class="fa-solid fa-tools"></i>{{$contract->workers()->count()}}</td>
    <th>
        <button class="btn btn-ghost btn-xs">details</button>
    </th>
</tr>
