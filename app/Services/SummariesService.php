<?php

namespace App\Services;

use App\Constants\RoleName;
use App\DateFormat;
use App\Enums\RequiredTimeUnit;
use App\Models\AcademicPeriod;
use App\Models\Contract;
use App\Models\User;
use App\Models\WorkerContract;
use Illuminate\Support\Collection;
use function GuzzleHttp\Promise\all;

class SummariesService
{
    const SUCCESS_REQUIREMENT=80/100;

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

        /* dummy data for fast testing
        for ($i=0;$i<3;$i++)
        {

            for($j=0;$j<16;$j++)
            {
                $seriesData["fin$i"]["boba".$j][]=["2023-05-".($j+1), $j, 10, 10, 01, 10, 'bob','max'];
            }

        }
        */


        $groupsSummary=[];
        $studentsSummary=[];
        collect($seriesData)->each(function($groupData,$groupName) use (&$studentsSummary, &$groupsSummary){
            $groupSuccessCount=0;
            $groupFailureCount=0;
            $groupSuccessDetails=[];
            $groupFailureDetails=[];

            collect($groupData)->each(function($studentData,$studentName) use(&$studentsSummary,&$groupSuccessCount,&$groupFailureCount,$groupsSummary,&$groupSuccessDetails,&$groupFailureDetails){

                [$studentSuccessTime,$studentTotalTime,$studentSuccessProjects, $studentFailureProjects] = collect($studentData)->reduceSpread(
                    fn(int $successTime,int $totalTime,array $studentSuccessProjects,array $studentFailureProjects,$pointData)=>
                        [
                            $successTime+$pointData[4],
                            $totalTime+$pointData[5],
                            $successTime==0?$studentSuccessProjects:array_merge($studentSuccessProjects,[$pointData[6]]),
                            $successTime>0?$studentFailureProjects:array_merge($studentFailureProjects,[$pointData[6]]),
                        ],0,0,[],[]);

                $studentsSummary[$studentName]=[$studentSuccessTime,$studentSuccessProjects,$studentTotalTime,$studentFailureProjects];

                $studentSuccess = $studentSuccessTime/$studentTotalTime>self::SUCCESS_REQUIREMENT;

                if($studentSuccess){
                    $groupSuccessCount++;
                    $groupSuccessDetails[]=$studentName;
                }else{
                    $groupFailureCount++;
                    $groupFailureDetails[]=$studentName;
                }

            });

            $groupsSummary[$groupName]=[$groupSuccessCount,$groupSuccessDetails,$groupFailureCount,$groupFailureDetails];

        });

        //$seriesDataCol->groupBy();

        //Compute students stat
        //$studentsSummary=$seriesDataCol->flatMap()
        //$flat = $seriesDataCol->map(function (string $group,int $groupKey){
           //collect($)
        //});

        //Compute groups stats

        //Compute projects stats
        //Additionne les pourcentages par projet
        //collect($data)->flatten(2)->groupBy(fn($eval)=>$eval[2])->map(fn($project)=>$project->reduce(fn($carry,$eval2)=>$carry+$eval2[1],0))

        //collect($data)->flatten(2)->groupBy(fn($eval)=>$eval[2])->map(fn($project)=>$project->reduce(fn($carry,$eval2)=>[$carry[0]+$eval2[1],'bob'.$carry[1]],[0,'max']))

        if(sizeof($seriesData)>0){
            return json_encode([
                'evaluations'=>$seriesData,
                'summaries'=>['students'=>$studentsSummary,'groups'=>$groupsSummary],
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
        $totalTime = 0;
        $totalSuccessTime = 0;
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
                    $totalTime=0;
                    $totalSuccessTime=0;
                }

                $group = $groupMember->group->groupName->name;
                $success = $wContract->success;
                $project = $contract->jobDefinition->title;
                $date = $wContract->success_date;
                $time = $contract->jobDefinition->getAllocatedTime($timeUnit);

                $successTime = $success ? $time : 0;

                $totalTime += $time;
                $totalSuccessTime += $successTime;

                $formattedDate = $date->format("Y-m-d h:i");
                $percentage = round($totalSuccessTime / $totalTime * 100);

                $clients = $contract->clients->transform(fn($client)=>$client->getFirstnameL())->implode(',');

                //ATTENTION: for echarts series format (as currently used), first 2 infos are X and Y ...
                $seriesData[$group][$workerName][] = [$formattedDate, $percentage, $successTime, $time, $totalSuccessTime, $totalTime, $project,$clients];

                //Idea of evolution for easier data post-processing:
                // $seriesData[]=['group'=>$group,'worker'=>$workerName,'data'=>[$formattedDate, $percentage, $successTime, $time, $totalSuccessTime, $totalTime, $project,$clients]];
            }
        }

        return $seriesData;
    }
}

