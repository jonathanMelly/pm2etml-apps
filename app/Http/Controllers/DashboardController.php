<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\User;
use App\Models\JobDefinition;
use Illuminate\Database\Query\Builder;
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
        $contracts = $user->contractsAsAWorker()->get()->union(
            $user->contractsAsAClient()->get());


        return view('dashboard')->with(compact('contracts'));
    }
}
