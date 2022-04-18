<?php

namespace App\Http\Controllers;

use App\Constants\RoleName;
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
        /*
        //$user->
        $jobs = JobDefinition::published()
            //->where('')
            ->orderBy('required_xp_years')
            ->orderBy('priority')
            ->get();
        */


        $view = view('dashboard');

        if($user->hasAnyRole(RoleName::TEACHER,RoleName::STUDENT))
        {
            if($user->hasRole(RoleName::TEACHER))
            {
                $query =  $user->contractsAsAClient();
            }
            else
            {
                $query = $user->contractsAsAWorker();
            }

            $contracts = $query->with('jobDefinition') //eager load definitions as always needed

                ->orderByDesc('end')
                ->orderByDesc('start')

                ->get();

            return $view->with(compact('contracts'));
        }
        else
        {
            return $view;
        }

    }
}
