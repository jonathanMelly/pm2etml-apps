<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Evaluation - {{ $evaluation->student->firstname }} {{ $evaluation->student->lastname }}</title>
    <style>
        @page { margin: 25px; }
        body { font-family: sans-serif; color: #333; line-height: 1.3; font-size: 11px; }
        
        /* Header */
        .header-table { width: 100%; border-bottom: 2px solid #4f46e5; margin-bottom: 15px; padding-bottom: 10px; }
        .title { font-size: 20px; font-weight: bold; color: #111; margin: 0; }
        .subtitle { font-size: 12px; color: #666; margin-top: 2px; }
        
        /* Info Grid */
        .info-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .info-table td { vertical-align: top; padding: 0 10px 0 0; }
        .label { font-size: 9px; text-transform: uppercase; color: #888; font-weight: bold; }
        .value { font-size: 12px; font-weight: bold; color: #111; }
        
        /* Global Score */
        .score-box { 
            background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; 
            padding: 8px 15px; text-align: center;
        }
        .score-val { font-size: 24px; font-weight: bold; color: #4f46e5; line-height: 1; }
        .score-lbl { font-size: 9px; text-transform: uppercase; color: #666; font-weight: bold; }

        /* Columns Layout using Table */
        .layout-table { width: 100%; border-collapse: collapse; }
        .layout-table td { vertical-align: top; }
        .col-spacer { width: 2%; }
        .col-content { width: 49%; }
        
        /* Criterion Card */
        .criterion { 
            margin-bottom: 10px; 
            border: 1px solid #e5e7eb; 
            border-radius: 6px; 
            background-color: #fff;
        }
        .criterion-header { 
            background-color: #f3f4f6; 
            padding: 6px 10px; 
            border-bottom: 1px solid #e5e7eb;
        }
        .header-content { width: 100%; }
        .c-title { font-weight: bold; font-size: 12px; text-align: left; }
        .c-score { font-weight: bold; font-size: 12px; text-align: right; }
        
        .rating-na, .rating-NA { color: #dc2626; }
        .rating-pa, .rating-PA { color: #d97706; }
        .rating-a, .rating-A { color: #2563eb; }
        .rating-la, .rating-LA { color: #16a34a; }
        
        .criterion-body { padding: 8px 10px; min-height: 20px; }
        .remark { font-size: 11px; color: #444; font-style: italic; margin: 0; }
        .no-remark { color: #999; font-size: 10px; margin: 0; }

        /* General Remark */
        .general-remark { 
            margin-top: 15px; 
            border: 1px solid #e5e7eb; 
            border-radius: 6px; 
            padding: 10px; 
            background-color: #fff;
        }
        .gr-title { font-size: 12px; font-weight: bold; margin-bottom: 5px; color: #111; border-bottom: 1px solid #eee; padding-bottom: 3px; }
        .gr-text { font-size: 11px; color: #333; white-space: pre-wrap; }

        .footer { 
            position: fixed; bottom: -20px; left: 0; right: 0;
            font-size: 9px; color: #999; text-align: center; border-top: 1px solid #eee; padding-top: 5px; 
        }
    </style>
</head>
<body>
    {{-- Header Table --}}
    <table class="header-table">
        <tr>
            <td width="70%">
                <h1 class="title">Rapport d'évaluation</h1>
                <div class="subtitle">Pulse Evaluation • {{ $evaluation->jobDefinition->title }}</div>
            </td>
            <td width="30%" align="right">
                <table class="score-box" align="right">
                    <tr>
                        <td>
                            <div class="score-lbl">Résultat</div>
                            <div class="score-val">{{ $globalScore }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Info Table --}}
    <table class="info-table">
        <tr>
            <td width="33%">
                <div class="label">Étudiant</div>
                <div class="value">{{ $evaluation->student->firstname }} {{ $evaluation->student->lastname }}</div>
            </td>
            <td width="33%">
                <div class="label">Enseignant</div>
                <div class="value">{{ $evaluation->teacher->firstname }} {{ $evaluation->teacher->lastname }}</div>
            </td>
            <td width="33%">
                <div class="label">Période</div>
                <div class="value">
                    {{ $evaluation->start_date ? $evaluation->start_date->format('d.m.Y') : '' }} 
                    - 
                    {{ $evaluation->end_date ? $evaluation->end_date->format('d.m.Y') : '' }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Columns Layout Table --}}
    @php
        $chunks = $criteria->chunk(ceil($criteria->count() / 2));
        $leftCriteria = $chunks->get(0) ?? collect();
        $rightCriteria = $chunks->get(1) ?? collect();
    @endphp

    <table class="layout-table">
        <tr>
            <td class="col-content">
                @foreach($leftCriteria as $criterion)
                    @include('eval_pulse.pdf_criterion_item', ['criterion' => $criterion, 'latestVersion' => $latestVersion])
                @endforeach
            </td>
            <td class="col-spacer"></td>
            <td class="col-content">
                @foreach($rightCriteria as $criterion)
                    @include('eval_pulse.pdf_criterion_item', ['criterion' => $criterion, 'latestVersion' => $latestVersion])
                @endforeach
            </td>
        </tr>
    </table>

    <div class="general-remark">
        <div class="gr-title">Remarque Générale</div>
        <div class="gr-text">{{ $latestVersion->generalRemark ? $latestVersion->generalRemark->body : 'Aucune remarque générale.' }}</div>
    </div>

    <div class="footer">
        Document confidentiel • Généré le {{ now()->format('d.m.Y H:i') }}
    </div>
</body>
</html>
