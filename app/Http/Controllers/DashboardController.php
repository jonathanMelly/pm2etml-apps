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
            if($user->hasRole(RoleName::TEACHER))
            {
                //TODO convert into powerRelation to avoid hard-coded table names...
                $sqlQuery = "
                select jd.*,min(c.start) as min_start,max(c.end) as max_end,count(c.id) as contracts_count from job_definitions jd
                    inner join contracts c on c.job_definition_id=jd.id
                    inner join contract_client cc on cc.contract_id=c.id and cc.user_id=?

                    inner join contract_worker cw on cw.contract_id=c.id
                        inner join group_members gm on cw.group_member_id=gm.id
                            inner join groups g on gm.group_id=g.id
                                inner join academic_periods ap on g.academic_period_id=ap.id and ap.id=?

                    group by c.job_definition_id

                    order by min(c.`end`)
                ";

                $jobs= JobDefinition::fromQuery($sqlQuery,[$user->id,AcademicPeriod::current()]);

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
