<x-app-layout>

    <div
        class="sm:mx-6 bg-base-200 bg-opacity-50 rounded-box border border-accent border-opacity-25 p-1 flex flex-row flex-wrap items-center justify-around">
        {{-- FILTER --}}
        <form name="filter" method="get">

            <x-job-select-xp-years class="select-sm" :all="true" :old="request('required_xp_years')" />

            <x-job-select-priority class="select-sm" :all="true" :old="request('priority')" />

            <select class="select select-sm" name="size">
                <option selected value="">{{__('Any size')}}</option>
                @foreach(['sm'=>'Small','md'=>'Medium','lg'=>'Large'] as $size=>$sizeText)
                    <option @selected(request('size')==$size) value="{{$size}}">{{__($sizeText)}}</option>
                @endforeach
            </select>

            <select class="select select-sm" name="provider">
                <option selected value="">{{__('Any provider')}}</option>
                @foreach($providers as $provider)
                    <option @selected(request('provider')==$provider->id) value="{{$provider->id}}">[{{$provider->getAcronym()}}] {{$provider->getFirstnameLX(3)}}</option>
                @endforeach
            </select>

            @can('jobDefinitions.create')
            <select class="select select-sm" name="status">
                <option @selected(request('status')=='exclude') value="exclude">{{__('Published')}}</option>
                <option @selected(request('status')=='include') value="include">{{__('With drafts')}}</option>
                <option @selected(request('status')=='only') value="only">{{__('Drafts only')}}</option>
                <option @selected(request('status')=='trashed') value="trashed">{{__('With trashed')}}</option>
            </select>
            @endcan

            <input value="{{request('fulltext')}}" name="fulltext" type="text" placeholder="{{__('Text search')}}" class="input input-sm"/>


            <button class="btn btn-sm btn-warning bg-opacity-75 hover:bg-opacity-100">{{__('Filter')}}</button>

            <button class="btn btn-sm btn-outline" type="button" onclick="window.location.href='{{route('marketplace')}}'">{{__('View all')}}</button>

        </form>
    </div>

    <div class="sm:mx-6 flex flex-row flex-wrap items-center justify-between">
    @can('jobDefinitions.create')
        <a class="btn btn-sm btn-primary btn-outline my-2" href="{{route('jobDefinitions.create')}}">
            <i class="fa-solid fa-plus fa-lg mr-2"></i>{{__('Add a new job')}}
        </a>
    @endcan
        <div class="flex flex-row flex-nowrap badge badge-info bg-opacity-50">
            <img src="{{url('img/sigma.svg')}}" class="w-6 -ml-2">
            <div class="ml-1 self-center text-base-content">{{trans_choice(':number project|:number projects',$definitions,['number'=>$definitions->count()])}}</div>
        </div>
    </div>

    <div class="sm:mx-6 bg-base-200 rounded-box sm:p-3 p-1" x-data="{jobNameToDelete:''}">

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-1 sm:gap-2 md:gap-3">
            @forelse ($definitions as  $definition)
                <x-job-definition-card :job="$definition"
                                       :view-only="Auth::user()->cannot('jobs-apply') || Auth::user()->isAdmin()"/>
            @empty
                <p>{{ __('No jobs') }}</p>
            @endforelse
        </div>

        <x-job-definition-card-delete-modal/>

    </div>

</x-app-layout>
