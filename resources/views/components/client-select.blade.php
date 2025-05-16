@php use App\Models\JobDefinitionPart; @endphp
@props([
    'jobDefinition',
    'parts'=>null /*jobdef subparts*/,
    'withStats'=>true,
    'selected'=>'',
    'name'=>null
    ])
@php
/* @var $jobDefinition \App\Models\JobDefinition */
    if($parts===null){
        $mainJob = JobDefinitionPart::make();
        $mainJob->id=0;
        $parts=collect()->add($mainJob);
    }

    //todo load this globally for better perf...
    //Cache will be forgotten (emptied) upon job modification in \App\Http\Controllers\JobDefinitionController::storeUpdate
    $providers = Cache::rememberForever('providers-'.$jobDefinition->id, function () use ($jobDefinition) {  return $jobDefinition->getProviders(); });

    //If a user is deleted/added, it will be reflected only 5 minutes later...
    $clients = Cache::rememberForever('clients-'.$jobDefinition->id,function () use ($jobDefinition,$providers) {  return $jobDefinition->getClients($providers); });
@endphp

@foreach($parts as $part)
    <label class="input-group">
        @php
            $size2="w-full";
            if(!stringNullOrEmpty($part->name)){
                $size="w-1/3";
                $size2="w-2/3";
            }
        @endphp

        @if(!stringNullOrEmpty($part->name))
            <span class="{{$size}}">{{$part->name}}</span>
        @endif

        <select class="select select-bordered {{$size2}}" name="{{$name??'client-'.$part->id}}">
            <option disabled selected>{{__('Client')}}</option>

            @foreach($providers as $client)
                <option value="{{$client->id}}" {{old('client')==$client->id || $selected==$client->id?'selected="selected"':''}}>
                    {{$client->firstname.' '.$client->lastname}} ({{$client->getClientLoad(\App\Models\AcademicPeriod::current())['percentage']}}%)
                </option>
            @endforeach
            <option class="divider p-0 m-0"></option>
            @foreach($clients as $client)
                <option value="{{$client->id}}" {{old('client')==$client->id || $selected==$client->id?'selected="selected"':''}}>
                    {{$client->firstname.' '.$client->lastname}} ({{$client->getClientLoad(\App\Models\AcademicPeriod::current())['percentage']}}%)
                </option>
            @endforeach
        </select>

    </label>
@endforeach
