@props([
    'old'=>null,
    'all'=>false
    ])
<select {{ $attributes->merge(['class' => 'select']) }} name="priority" id="priority">
    @if($all)
        <option selected value="">{{__('Any priority')}}</option>
    @endif
    @foreach(\App\Enums\JobPriority::cases() as $priority)
        <option
            value="{{$priority->value}}" @selected($old != '' && $old==$priority->value)> {{--WARNING (int)'' => 0 !!!!! and empty(0) =true !!! --}}
            {{__(Str::ucfirst(Str::lower($priority->name)))}}</option>
    @endforeach
</select>
