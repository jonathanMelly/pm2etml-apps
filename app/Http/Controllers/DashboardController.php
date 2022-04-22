<?php

namespace App\Http\Controllers;

use App\Constants\RoleName;
use App\Models\AcademicPeriod;
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
            if($user->hasRole(RoleName::TEACHER))
            {

                $query = $user->jobDefinitions()
                    //Filter contracts with current client (as a job has various possible providers)
                    ->whereHas('contracts.clients',fn($q)=>$q->where('user_id','=',$user->id))

                    //Filter Period
                    ->whereHas('contracts.workers.group.academicPeriod',
                        fn($q)=>$q->whereId(AcademicPeriod::current()));

                $jobs = $query->get();

                return $view->with(compact('jobs'));
            }
            else
            {
                $query = $user->contractsAsAWorker()
                    ->with('jobDefinition') //eager load definitions as needed on UI
                    ->with('clients') //eager load clients as needed on UI

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
