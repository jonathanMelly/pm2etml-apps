<?php

namespace App\Http\Controllers;

use App\Exports\EvaluationsExport;
use App\Services\SummariesService;
use App\SwissFrenchDateFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class EvaluationExportController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, SummariesService $summariesService)
    {
        /* @var $evalData Collection*/
        $evalData = $summariesService->getEvaluationsSummary(
            Auth::user(),
            $request->get('academicPeriodId'),
            $request->get('timeUnit'),
            json: false
        );

        return Excel::download(new EvaluationsExport($evalData),
            'inf-prat-'.now()->format(SwissFrenchDateFormat::DATE_TIME_FS).'.xlsx');
    }
}
