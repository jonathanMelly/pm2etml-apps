<tr>
    <td>
        <div class="flex items-center space-x-3 hover:cursor-pointer" @click="show{{$job->id}} = ! show{{$job->id}}">
            <i class="fa-solid fa-xl" :class="show{{$job->id}}?'fa-caret-down':'fa-caret-right'"></i>
            <div class="avatar" >
                <div class="mask mask-squircle w-12 h-12">
                    <img src="{{img($job->image)}}" alt="{{$job->name}}" />
                </div>
            </div>
            <div>
                <div class="font-bold">{{$job->name}}</div>
            </div>
        </div>
    </td>
    <td>
        <i class="fa-solid fa-calendar-day"></i> {{\Illuminate\Support\Carbon::parse($job->min_start)->format(\App\SwissFrenchDateFormat::FORMAT)}}
    </td>
    <td>
        <i class="fa-solid fa-calendar-days"></i> {{\Illuminate\Support\Carbon::parse($job->max_end)->format(\App\SwissFrenchDateFormat::FORMAT)}}
    </td>
    <td><i class="fa-solid fa-users"></i> {{$job->contracts_count}}</td>
    <td>
        <button class="btn btn-ghost btn-xs">details</button>
    </td>
</tr>
