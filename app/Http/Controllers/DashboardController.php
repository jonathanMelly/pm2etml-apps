<?php

namespace App\Http\Controllers;

use App\Constants\RoleName;
use App\Models\AcademicPeriod;
use App\Models\Contract;
use App\Models\JobDefinition;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

        $user = auth()->user();

        $view = view('dashboard');

        if($user->hasAnyRole(RoleName::TEACHER,RoleName::STUDENT))
        {
            //Client
            if($user->hasRole(RoleName::TEACHER))
            {

                $jobs = $user->getJobDefinitionsWithActiveContracts(AcademicPeriod::current());

                return $view->with(compact('jobs'));
            }
            else
            //Workers
            {
                $query = $user->contractsAsAWorker()
                    ->with('jobDefinition') //eager load definitions as needed on UI
                    ->with('clients') //eager load clients as needed on UI
                    ->with('workersContracts')

                    ->orderByDesc('end')
                    ->orderByDesc('start');

                $contracts = $query->get();

                return $view->with(compact('contracts'));
            }


        }
        else
        {
            return $view;
        }

    }
}
