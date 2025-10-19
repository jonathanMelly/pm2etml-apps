<x-app-layout>
    <div class="sm:mx-6">
        <div class="prose mb-4">
            <h1>{{ __('Export Journal') }}</h1>
            <div>{{ __('Projet') }}: {{ $workerContract->contract?->jobDefinition?->title }}</div>
            <div>{{ __('Élève') }}: {{ $workerContract->groupMember?->user?->getFirstnameL() }}</div>
        </div>

        <div class="print:hidden mb-3">
            <button class="btn btn-primary" onclick="window.print()"><i class="fa-solid fa-print mr-1"></i>{{ __('Imprimer / Exporter en PDF') }}</button>
        </div>

        <style>
            @media print {
                .print\:hidden { display: none !important; }
                .page-break { page-break-before: always; }
            }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 6px; }
            th { background: #f5f5f5; }
        </style>

        <table>
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Périodes') }}</th>
                    <th>{{ __('Précision (min)') }}</th>
                    <th>{{ __('Détails') }}</th>
                    <th>{{ __('Appréciation') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ $log->date->format(\App\SwissFrenchDateFormat::DATE) }}</td>
                    <td class="text-center">{{ $log->periods_count }}</td>
                    <td class="text-center">{{ $log->precision_minutes }}</td>
                    <td>
                        @if(is_array($log->periods))
                            <ul>
                                @foreach($log->periods as $p)
                                    <li>
                                        <div><strong>{{ __('Période') }} {{ $p['index'] }}:</strong> {{ $p['minutes'] }}'</div>
                                        @if(!empty($p['lines']))
                                            <ul>
                                                @foreach($p['lines'] as $line)
                                                    @php($meta = config('journal.activity_types.' . ($line['type'] ?? ''), []))
                                                    <li>
                                                        @if(!empty($line['type']))
                                                            <span class="badge {{ $meta['color'] ?? 'badge-outline' }}">
                                                                <i class="fa-solid {{ $meta['icon'] ?? 'fa-circle' }}"></i>
                                                                <span class="ml-1">{{ __($line['type']) }}</span>
                                                            </span>
                                                        @endif
                                                        <span class="ml-1">{{ $line['text'] ?? '' }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                        @if(!empty($p['note']))
                                            <div><em>{{ $p['note'] }}</em></div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        @if($log->student_notes)
                            <div><strong>{{ __('Notes') }}:</strong> {{ $log->student_notes }}</div>
                        @endif
                    </td>
                    <td>{{ $log->appreciation }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <script>
        // Auto-open print dialog after load for convenience
        window.addEventListener('load', () => {
            setTimeout(() => { window.print(); }, 200);
        });
    </script>
</x-app-layout>
