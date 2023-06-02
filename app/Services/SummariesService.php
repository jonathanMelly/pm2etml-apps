<?php

namespace App\Services;

use App\Constants\RoleName;
use App\DateFormat;
use App\Enums\RequiredTimeUnit;
use App\Models\AcademicPeriod;
use App\Models\Contract;
use App\Models\User;
use App\Models\WorkerContract;

class SummariesService
{
    const SUCCESS_REQUIREMENT=80/100;

    const PI_DATE=0;
    const PI_PERCENTAGE=1;
    const PI_SUCCESS_TIME=2;
    const PI_TIME=3;
    const PI_ACCUMULATED_SUCCESS_TIME=4;
    const PI_ACCUMULATED_TIME=5;
    const PI_PROJECT=6;
    const PI_CLIENTS=7;

    public function getEvaluationsSummary(User $user,int $academicPeriodId,int $_timeUnit):string{

        $seriesData=[];
        $timeUnit = RequiredTimeUnit::from($_timeUnit);

        if($user->hasRole(RoleName::STUDENT)){
            $groupMember = $user->groupMember($academicPeriodId);

            $wContractsBaseQuery = WorkerContract::where('group_member_id','=',$groupMember->id);

        }else if($user->hasAnyRole(RoleName::TEACHER,RoleName::ADMIN)){
            //Prof de classe
            $handledGroups = $user->getGroupNames($academicPeriodId,false);

            $wContractsBaseQuery =
                WorkerContract::query();

            //Dean and principal gets all info
            if(!$user->hasAnyRole(RoleName::PRINCIPAL,RoleName::DEAN,RoleName::ADMIN))
            {

                $wContractsBaseQuery->where(function ($query) use($handledGroups,$user){

                    return $query
                        // Class teacher
                        ->whereHas('groupMember.group.groupName',fn($q)=>$q->whereIn('name',$handledGroups))

                        //Project manager (client)
                        ->orWhereHas('contract.clients.groupMembers.user',fn($q)=>$q->where('id','=',$user->id));
                });
            }

            $wContractsBaseQuery->whereRelation('groupMember.group.academicPeriod','id','=',$academicPeriodId);

        }

        if(isset($wContractsBaseQuery)){
            $wContracts = $wContractsBaseQuery
                ->with('contract.jobDefinition')


                ->orderBy('group_member_id')
                ->orderBy('success_date')

                ->get();

            $seriesData = array_merge($seriesData, $this->buildSeriesData($wContracts, $timeUnit));
        }

        $period = AcademicPeriod::whereId($academicPeriodId)->firstOrFail();

        $startZoom = now()->addMonth(-3);
        if($startZoom->isBefore($period->start)){
            $startZoom=$period->start;
        }

         //dummy data for fast testing
        /*
        if(app()->environment('local') && $user->hasRole(RoleName::TEACHER)) {
            for ($i = 0; $i < 3; $i++) {
                for ($j = 0; $j < 16; $j++) {

                    $totalSuccess=0;
                    $totalTime=0;
                    for($k=0;$k<4;$k++){
                        $time=random_int(24,60);

                        $success = random_int(0, 1)*$time;

                        $totalTime+=$time;
                        $totalSuccess+=$success;

                        $seriesData["cid$i"."a"]["prenom nome1456789212345678" . $i.$j][] = ["2023-".(3+$k)."-10", $totalSuccess/$totalTime, $success, $time, 01, 10, 'projectx'.$k, 'mark z.'];
                    }

                }

            }
        }
        */



        //Compute students and group stats
        $summaries=[];
        collect($seriesData)->each(function($groupData,$groupName) use (&$summaries){
            $groupSuccessCount=0;
            $groupFailureCount=0;
            $groupSuccessDetails=[];
            $groupFailureDetails=[];

            collect($groupData)->each(function($studentData,$studentName) use(&$summaries,&$groupSuccessCount,&$groupFailureCount,&$groupSuccessDetails,&$groupFailureDetails,$groupName){

                [$studentSuccessTime,$studentTotalTime,$studentSuccessProjects, $studentFailureProjects] = collect($studentData)->reduceSpread(
                    fn(int $totalSuccessTime, int $totalTime, array $studentSuccessProjects, array $studentFailureProjects, $pointData)=>
                        [
                            $totalSuccessTime+$pointData[self::PI_SUCCESS_TIME],
                            $totalTime+$pointData[self::PI_TIME],
                            $pointData[self::PI_SUCCESS_TIME]==0?$studentSuccessProjects:array_merge($studentSuccessProjects,[$pointData[self::PI_PROJECT]]),
                            $pointData[self::PI_SUCCESS_TIME]>0?$studentFailureProjects:array_merge($studentFailureProjects,[$pointData[self::PI_PROJECT]]),
                        ],0,0,[],[]);

                $summaries[$groupName][$studentName]=[$studentSuccessTime,$studentSuccessProjects,$studentTotalTime-$studentSuccessTime,$studentFailureProjects];

                $studentSuccess = $studentSuccessTime/$studentTotalTime>self::SUCCESS_REQUIREMENT;

                if($studentSuccess){
                    $groupSuccessCount++;
                    $groupSuccessDetails[]=$studentName;
                }else{
                    $groupFailureCount++;
                    $groupFailureDetails[]=$studentName;
                }

            });

            //Put 'all' at the beginning for easier post processing
            $summaries[$groupName]=array_merge(['all'=>[$groupSuccessCount,$groupSuccessDetails,$groupFailureCount,$groupFailureDetails]],$summaries[$groupName]);
            //$summaries[$groupName];

        });

        //Compute projects stats
        //Additionne les pourcentages par projet
        //collect($data)->flatten(2)->groupBy(fn($eval)=>$eval[2])->map(fn($project)=>$project->reduce(fn($carry,$eval2)=>$carry+$eval2[1],0))

        //collect($data)->flatten(2)->groupBy(fn($eval)=>$eval[2])->map(fn($project)=>$project->reduce(fn($carry,$eval2)=>[$carry[0]+$eval2[1],'bob'.$carry[1]],[0,'max']))

        if(sizeof($seriesData)>0){
            return json_encode([
                'evaluations'=>$seriesData,
                'summaries'=>$summaries,
                'datesWindow'=>[
                    $period->start->format(DateFormat::ECHARTS_FORMAT),
                    $period->end->format(DateFormat::ECHARTS_FORMAT),
                    $startZoom->format(DateFormat::ECHARTS_FORMAT),
                    now()->addDay(5)->format(DateFormat::ECHARTS_FORMAT)
                ],
                'groupsCount'=>count($seriesData)
            ]);
        }
        return "{}";



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
        $accumulatedTime = 0;
        $accumulatedSuccessTime = 0;
        $previousWorkerName="";

        foreach ($wContracts as /* @var $wContract WorkerContract */ $wContract) {
            /* @var $contract Contract */
            $contract = $wContract->contract;

            if ($wContract->alreadyEvaluated()) {

                $groupMember = $wContract->groupMember;
                $worker = $groupMember->user;
                $workerName = $worker->getFirstnameL();
                //Worker switch, reset accumulators
                //[for perf reasons, all data is fetched with 1 request with all contracts sorted by userid...]
                if($workerName!=$previousWorkerName){
                    $previousWorkerName=$workerName;
                    $accumulatedTime=0;
                    $accumulatedSuccessTime=0;
                }

                $group = $groupMember->group->groupName->name;
                $success = $wContract->success;
                $project = $contract->jobDefinition->title;
                $date = $wContract->success_date;
                $time = $contract->jobDefinition->getAllocatedTime($timeUnit);

                $successTime = $success ? $time : 0;

                $accumulatedTime += $time;
                $accumulatedSuccessTime += $successTime;

                $formattedDate = $date->format("Y-m-d h:i");
                $percentage = round($accumulatedSuccessTime / $accumulatedTime * 100);

                $clients = $contract->clients->transform(fn($client)=>$client->getFirstnameL())->implode(',');

                //ATTENTION: for echarts series format (as currently used), first 2 infos are X and Y ...
                $seriesData[$group][$workerName][] = [
                    self::PI_DATE=> $formattedDate,
                    self::PI_PERCENTAGE=> $percentage,
                    self::PI_SUCCESS_TIME => $successTime,
                    self::PI_TIME=> $time,
                    self::PI_ACCUMULATED_SUCCESS_TIME=> $accumulatedSuccessTime,
                    self::PI_ACCUMULATED_TIME => $accumulatedTime,
                    self::PI_PROJECT => $project,
                    self::PI_CLIENTS=> $clients];

                //Idea of evolution for easier data post-processing:
                // $seriesData[]=['group'=>$group,'worker'=>$workerName,'data'=>[$formattedDate, $percentage, $successTime, $time, $totalSuccessTime, $totalTime, $project,$clients]];
            }
        }

        return $seriesData;
    }
}

