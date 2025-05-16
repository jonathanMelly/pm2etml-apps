<?php

namespace App\Http\Controllers;

use App\Constants\RoleName;
use App\Models\User;
use App\Services\SummariesService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return Application|Factory|View|\Illuminate\Foundation\Application|void
     */
    public function __invoke(Request $request, SummariesService $statsService)
    {

        $view = view('dashboard');
        $user = auth()->user();

        if ($user->hasAnyRole(RoleName::TEACHER, RoleName::STUDENT, RoleName::PRINCIPAL, RoleName::DEAN, RoleName::ADMIN)) {
            $contracts = null;
            $jobs = null;
            $past_contracts = collect();
            $periodId = $request->get('academicPeriodId');

            //Teacher
            if ($user->hasRole(RoleName::TEACHER)) {
                //Get jobs as a client
                $jobs = $user->getJobDefinitionsWithActiveContracts($periodId);

                $candidatesForWork = User::role(RoleName::STUDENT)
                    ->whereHas('groupMembers.group.academicPeriod', fn ($q) => $q->whereId($periodId))
                    ->get();

                $result = $view->with(compact('jobs', 'candidatesForWork'));
            } else { //Students (auto filtered on student periodId as using groupmember...)
                //Get jobs as Workers
                $query = $user->contractsAsAWorker()
                    ->with('jobDefinition') //eager load definitions as needed on UI
                    ->with('clients') //eager load clients as needed on UI
                    ->with('workersContracts')

                    ->orderByDesc('end')
                    ->orderByDesc('start');

                $contracts = $query->get();
                $result = $view->with(compact('contracts'));
                $past_contracts = Cache::rememberForever($user->id.$periodId,fn() => $user->pastContractsAsAWorker($periodId)->get());

            }

            //Append evaluations summary
            $evaluationsSummaryJsObject = $statsService->getEvaluationsSummary(
                $user,
                $periodId,
                $request->get('timeUnit')
            );

            return $result->with(compact('evaluationsSummaryJsObject', 'periodId','past_contracts'));

        } else {
            abort(403, 'Missing required role');
        }

    }
}
