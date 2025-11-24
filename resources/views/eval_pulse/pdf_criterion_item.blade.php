@php
    $appreciation = $latestVersion->appreciations->where('criterion_id', $criterion->id)->first();
    $isIgnored = $appreciation ? $appreciation->is_ignored : false;
    $value = $appreciation && !$isIgnored ? $appreciation->value : '-';
    $remark = ($appreciation && !$isIgnored && $appreciation->remark) ? $appreciation->remark->body : null;
@endphp

<div class="criterion">
    <div class="criterion-header">
        <table class="header-content">
            <tr>
                <td class="c-title">{{ $criterion->name }}</td>
                <td class="c-score">
                    @if($isIgnored)
                        <span style="color: #999; font-weight: normal;">(Ignoré)</span>
                    @else
                        <span class="rating-{{ $value }}">{{ $value }}</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
    <div class="criterion-body">
        @if(!$isIgnored && $remark)
            <p class="remark">{{ $remark }}</p>
        @elseif($isIgnored)
            <p class="no-remark">Critère non évalué</p>
        @else
            <p class="no-remark">Aucune remarque</p>
        @endif
    </div>
</div>
