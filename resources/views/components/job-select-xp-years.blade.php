@props([
    'old'=>null,
    'all'=>false
    ])
<select {{ $attributes->merge(['class' => 'select']) }} name="required_xp_years" id="required_xp_years">
    @if($all)
        <option selected value="">{{__('Any experience')}}</option>
    @endif
    @for($i=1;$i<5;$i++)
        <option
            value="{{$i-1}}" @selected($old != '' && $old == $i-1)> {{--WARNING (int)'' => 0 !!!!! --}}
            {!!$i.'<sup>'.__(ordinal($i)).'</sup>&nbsp;'.__('year')!!}</option>
    @endfor
</select>
