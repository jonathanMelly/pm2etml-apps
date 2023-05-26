<?php

namespace App\Http\Controllers;

use App\Constants\RoleName;
use App\Services\SummariesService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, SummariesService $statsService)
    {

        $view = view('dashboard');
        $user = auth()->user();

        if($user->hasAnyRole(RoleName::TEACHER,RoleName::STUDENT,RoleName::PRINCIPAL,RoleName::DEAN,RoleName::ADMIN))
        {
            $contracts =null;
            $jobs=null;

            //Teacher
            if($user->hasRole(RoleName::TEACHER))
            {
                //Get jobs as a client
                $jobs = $user->getJobDefinitionsWithActiveContracts($request->get("academicPeriodId"));

                $result = $view->with(compact('jobs'));
            }
            else
            //Students
            {
                //Get jobs as Workers
                $query = $user->contractsAsAWorker()
                    ->with('jobDefinition') //eager load definitions as needed on UI
                    ->with('clients') //eager load clients as needed on UI
                    ->with('workersContracts')

                    ->orderByDesc('end')
                    ->orderByDesc('start');

                $contracts = $query->get();
                $result = $view->with(compact('contracts'));
            }

            //Append evaluations summary
            $evaluationsSummaryJsObject = $statsService->getEvaluationsSummary(
                $user,
                $request->get("academicPeriodId"),
                $request->get("timeUnit")
            );

            return  $result->with(compact('evaluationsSummaryJsObject'));

        }
        else
        {
            abort(403,"Missing required role");
        }

    }
}
