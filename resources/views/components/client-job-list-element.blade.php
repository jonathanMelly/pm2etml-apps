<tr x-show="$store.show{{$job->id}}main">
    <td>
        <div class="flex items-center space-x-3" >
            <i class="fa-solid fa-xl hover:cursor-pointer" :class="$store.show{{$job->id}}?'fa-caret-down':'fa-caret-right'" @click="$store.show{{$job->id}} = ! $store.show{{$job->id}}"></i>

            <a href="{{route('jobDefinitions.show',['jobDefinition'=>$job->id])}}" class="flex flex-row items-center space-x-3">
                <div class="avatar" >
                    <div class="mask mask-squircle w-12 h-12">
                        <img src="{{route('dmz-asset',['file'=>$job->image?->storage_path])}}" alt="{{$job->title}}" />
                    </div>
                </div>
                <div>
                    <div class="font-bold">{{Str::words($job->title,5)}}</div>
                </div>
            </a>
        </div>
    </td>
    <td class="text-center">
        <i class="fa-solid fa-fire-burner"></i> {{$job->getAllocationDetails()}}
    </td>
    <td>
        <i class="fa-solid fa-calendar-day"></i> {{\Illuminate\Support\Carbon::parse($job->min_start)->format(\App\SwissFrenchDateFormat::DATE)}}
    </td>
    <td>
        <i class="fa-solid fa-calendar-days"></i> {{\Illuminate\Support\Carbon::parse($job->max_end)->format(\App\SwissFrenchDateFormat::DATE)}}
    </td>
    <td><i class="fa-solid fa-users"></i> {{$job->contracts_count}}</td>
</tr>
