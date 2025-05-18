@props([
    'past'=>false
])
<td><i class="fa-solid fa-briefcase"></i> {{__('Job')}}</td>
<th><i class="fa-solid fa-sack-dollar"></i> {{__('Client')}}</th>
<x-contract-list-header :past="$past" />
