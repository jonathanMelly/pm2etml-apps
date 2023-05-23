<?php

namespace App\Services;

use App\Constants\RoleName;
use App\Enums\RequiredTimeUnit;
use App\Models\Contract;
use App\Models\User;
use App\Models\WorkerContract;

class SummariesService
{
    public function getEvaluationsSummary(User $user,int $academicPeriodId,int $_timeUnit):string{

        //TODO principal and dean and maitre de classe
        //Prof normal: voit la synthèse des contrats pour lesquels il a été client
        //Eleve : voit la synthèse des projets terminés ?
        //Maitre de classe: voit la synthèse des projets pour lesquels il est client + sa classe
        //Principal,dean: voit tout

        $seriesData=[];
        $timeUnit = RequiredTimeUnit::from($_timeUnit);

        if($user->hasRole(RoleName::STUDENT)){
            $groupMember = $user->groupMember($academicPeriodId);

            $wContractsBaseQuery = WorkerContract::where('group_member_id','=',$groupMember->id);

        }else if($user->hasRole(RoleName::TEACHER)){
            //Prof de classe
            $handledGroups = $user->getGroupNames($academicPeriodId,false);

            $wContractsBaseQuery =
                WorkerContract::
                whereHas('groupMember.group.groupName',fn($q)=>$q->whereIn('name',$handledGroups))
                //whereRelation('groupMember.group.groupName','name','in',$handledGroups)
                    ->whereRelation('groupMember.group.academicPeriod','id','=',$academicPeriodId);

            //maitre principal et doyen ?
        }

        if(isset($wContractsBaseQuery)){
            $wContracts = $wContractsBaseQuery
                ->with('contract.jobDefinition')

                ->orderBy('group_member_id')
                ->orderBy('success_date')

                ->get();

            $seriesData = array_merge($seriesData, $this->buildSeriesData($wContracts, $timeUnit));
        }

        return json_encode([
            "studentSeries"=>$seriesData,
        ]);

    }

    /**
     * @param \LaravelIdea\Helper\App\Models\_IH_WorkerContract_C|\Illuminate\Database\Eloquent\Collection|array $wContracts
     * @param RequiredTimeUnit $timeUnit
     * @param array $seriesData
     * @return array
     */
    public function buildSeriesData(\LaravelIdea\Helper\App\Models\_IH_WorkerContract_C|\Illuminate\Database\Eloquent\Collection|array $wContracts, RequiredTimeUnit $timeUnit): array
    {
        $seriesData=[];
        $totalTime = 0;
        $totalSuccessTime = 0;
        $previousWorkerName="";

        foreach ($wContracts as /* @var $wContract WorkerContract */ $wContract) {
            /* @var $contract Contract */
            $contract = $wContract->contract;

            if ($wContract->alreadyEvaluated()) {

                $workerName = $wContract->groupMember->user->getFirstnameL();
                //Worker switch, reset accumulators
                //[for perf reasons, all data is fetched with 1 request with all contracts sorted by userid...]
                if($workerName!=$previousWorkerName){
                    $previousWorkerName=$workerName;
                    $totalTime=0;
                    $totalSuccessTime=0;
                }

                $success = $wContract->success;
                $project = $contract->jobDefinition->title;
                $date = $wContract->success_date;
                $time = $contract->jobDefinition->getAllocatedTime($timeUnit);

                $successTime = $success ? $time : 0;

                $totalTime += $time;
                $totalSuccessTime += $successTime;

                $formattedDate = $date->format("Y-m-d");
                $percentage = round($totalSuccessTime / $totalTime * 100);

                $clients = $contract->clients->transform(fn($client)=>$client->getFirstnameL())->implode(',');

                //ATTENTION: for echarts series format (as currently used), first 2 infos are X and Y ...
                $seriesData[$workerName][] = [$formattedDate, $percentage, $successTime, $time, $totalSuccessTime, $totalTime, $project,$clients];
            }
        }

        return $seriesData;
    }
}

