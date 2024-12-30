@props([
    'past' => false,
])

<th><i class="fa-solid fa-briefcase"></i> {{ __('Job') }}</th>
<th><i class="fa-solid fa-sack-dollar"></i> {{ __('Client') }}</th>

@if (!$past)
    <th class="text-center" colspan="2"><i class="fa-solid fa-calendar"></i> {{ __('Remaining Days') }}</th>
    <th class="text-center"><i class="fa-solid fa-chart-line"></i> {{ __('Progress') }}</th>
@endif

<th class="text-center" colspan="2"><i class="fa-solid fa-wrench"></i> {{ __('Effort') }}</th>

@if (!$past)
    <th class="text-center" colspan="2"><i class="fa-solid fa-tools"></i> {{ __('Actions') }}</th>
@endif
