<a href="{{$link}}" target="_blank" class="card card-compact card-bordered border-gray-300 w-auto bg-base-100 shadow-xl hover:bg-gradient-to-b hover:from-primary/25 hover:to-base-100">
    <figure class="p-2">
        <img src="{{$logo}}" alt="{{$title}}" class="h-10">
    </figure>
    <div class="card-body">
        <h2 class="card-title">{{$title}}</h2>
        <p>{{$slot}}</p>

        <div class="card-actions justify-end">
            {{$tags}}
        </div>
    </div>

</a>
