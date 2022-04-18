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

        $contracts= ($user->hasRole(RoleName::TEACHER)?
            $user->contractsAsAClient(): $user->contractsAsAWorker())

            ->with('jobDefinition') //eager load definitions as always needed
            ->orderByDesc('end')
            ->orderByDesc('start')

            ->get();


        return view('dashboard')->with(compact('contracts'));
    }
}
