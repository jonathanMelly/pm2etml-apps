<tr>
    <td>
        <div class="flex items-center space-x-3">
            <div class="avatar">
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
        <i class="fa-solid fa-calendar-day"></i> {{$contracts->min('start')->format(\App\SwissFrenchDateFormat::FORMAT)}}
    </td>
    <td>
        <i class="fa-solid fa-calendar-days"></i> {{$contracts->max('end')->format(\App\SwissFrenchDateFormat::FORMAT)}}
    </td>
    <td><i class="fa-solid fa-users"></i> {{$contracts->count()}}</td>
    <td>
        <button class="btn btn-ghost btn-xs">details</button>
    </td>
</tr>
