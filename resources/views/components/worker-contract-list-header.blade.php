@props([
    'past'=>false
])
<td><i class="fa-solid fa-briefcase"></i> {{__('Job')}}</td>
<th><i class="fa-solid fa-sack-dollar"></i> {{__('Client')}}</th>
<x-contract-list-header :past="$past" />
@if(!$past)
<th class="text-center"><i class="fa-solid fa-list-check"></i> {{ __('Auto-évaluation') }}</th>
@endif
<th class="text-center"><i class="fa-solid fa-book"></i> {{ __('Journal') }}</th>
