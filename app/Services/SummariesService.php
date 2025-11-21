<?php

namespace App\Services;

use App\Constants\RoleName;
use App\DateFormat;
use App\Enums\RequiredTimeUnit;
use App\Models\AcademicPeriod;
use App\Models\Contract;
use App\Models\User;
use App\Models\WorkerContract;
use App\SwissFrenchDateFormat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LaravelIdea\Helper\App\Models\_IH_WorkerContract_C;

class SummariesService
{
    const SUCCESS_REQUIREMENT = 80 / 100;

    const SUCCESS_REQUIREMENT_IN_PERCENTAGE = self::SUCCESS_REQUIREMENT * 100;

    const PI_DATE = 0;

    const PI_CURRENT_PERCENTAGE = 1;

    const PI_SUCCESS_TIME = 2;

    const PI_TIME = 3;

    const PI_ACCUMULATED_SUCCESS_TIME = 4;

    const PI_ACCUMULATED_TIME = 5;

    const PI_PROJECT = 6;

    const PI_CLIENTS = 7;

    const PI_PROJECT_SPECIFIC = 8;

    const PI_DATE_SWISS = 9;

    const PI_SUCCESS_COMMENT = 10;

    const PI_REMEDIATION_STATUS = 11;

    /**
     * @return string | Collection data for chart OR raw collection
     */
    public function getEvaluationsSummary(User $user, int $academicPeriodId, int $_timeUnit, bool $json = true): string|Collection
    {

        $seriesData = [];
        $timeUnit = RequiredTimeUnit::from($_timeUnit);

        if ($user->hasRole(RoleName::STUDENT)) {
            $groupMember = $user->groupMember($academicPeriodId);

            if($groupMember===null)
            {
                Log::warning("Missing group member for user ".$user->id." and academic period ".$academicPeriodId);
                return '{}';
            }

            $wContractsBaseQuery = WorkerContract::where('group_member_id', '=', $groupMember->id);

        } elseif ($user->hasAnyRole(RoleName::TEACHER, RoleName::ADMIN)) {
            //Prof de classe
            $handledGroups = $user->getGroupNames($academicPeriodId, false);

            $wContractsBaseQuery =
                WorkerContract::query();

            //Dean and principal gets all info
            if (! $user->hasAnyRole(RoleName::PRINCIPAL, RoleName::DEAN, RoleName::ADMIN)) {

                $wContractsBaseQuery->where(function ($query) use ($handledGroups, $user) {

                    return $query
                        // Class teacher
                        ->whereHas('groupMember.group.groupName', fn ($q) => $q->whereIn('name', $handledGroups))

                        //Project manager (client)
                        ->orWhereHas('contract.clients', fn ($q) => $q->where(tbl(User::class).'.id', '=', $user->id));
                });
            }

            $wContractsBaseQuery->whereRelation('groupMember.group.academicPeriod', 'id', '=', $academicPeriodId);

        }

        if (isset($wContractsBaseQuery)) {
            $wContracts = $wContractsBaseQuery
                ->with('contract.jobDefinition')
                ->with('contract.clients')
                ->with('groupMember.group.groupName')
                ->with('groupMember.user')

                ->where('allocated_time', '>', 0)

                ->orderByPowerJoins('groupMember.group.groupName.year')
                ->orderByPowerJoins('groupMember.group.groupName.name')
                ->orderByPowerJoins('groupMember.user.lastname')
                ->orderByPowerJoins('groupMember.user.firstname')
                ->orderBy('success_date')

                ->get();

            $seriesData = array_merge($seriesData, $this->buildSeriesData($wContracts, $timeUnit, ! $json));
        }

        $period = AcademicPeriod::whereId($academicPeriodId)->firstOrFail();

        $startZoom = now()->addMonth(-3);
        if ($startZoom->isBefore($period->start)) {
            $startZoom = $period->start;
        }

        //dummy data for fast testing
        /*
                if(app()->environment('local') && $user->hasRole(RoleName::TEACHER)) {
                    $EVALS=4;
                    $STUDENTS=23;
                    $GROUPS=4;
                    for ($i = 0; $i < $GROUPS; $i++) {
                        for ($j = 0; $j < $STUDENTS; $j++) {

                            $totalSuccess=0;
                            $totalTime=0;
                            for($k=0;$k<$EVALS;$k++){
                                $time=random_int(24,60);

                                $success = random_int(0, 1)*$time;

                                $totalTime+=$time;
                                $totalSuccess+=$success;

                                $seriesData["test$i"."a"]["preno8" . $i.$j][] = ["2023-".(3+$k)."-10", $totalSuccess/$totalTime, $success, $time, 01, 10, 'projectx'.$k, 'mark z.'];
                            }

                        }

                    }
                }

        */

        //Compute students and group stats
        $summaries = [];
        collect($seriesData)->each(function ($groupData, $groupName) use (&$summaries) {
            $groupSuccessCount = 0;
            $groupFailureCount = 0;
            $groupSuccessDetails = [];
            $groupFailureDetails = [];

            collect($groupData)->each(function ($studentData, $studentName) use (&$summaries, &$groupSuccessCount, &$groupFailureCount, &$groupSuccessDetails, &$groupFailureDetails, $groupName) {

                [$studentSuccessTime,$studentTotalTime,$studentSuccessProjects, $studentFailureProjects] = collect($studentData)->reduceSpread(
                    fn (int $totalSuccessTime, int $totalTime, array $studentSuccessProjects, array $studentFailureProjects, $pointData) => [
                        $totalSuccessTime + $pointData[self::PI_SUCCESS_TIME],
                        $totalTime + $pointData[self::PI_TIME],
                        $pointData[self::PI_SUCCESS_TIME] == 0 ? $studentSuccessProjects : array_merge($studentSuccessProjects, [$pointData[self::PI_PROJECT]]),
                        $pointData[self::PI_SUCCESS_TIME] > 0 ? $studentFailureProjects : array_merge($studentFailureProjects, [$pointData[self::PI_PROJECT]]),
                    ], 0, 0, [], []);

                $summaries[$groupName][$studentName] = [$studentSuccessTime, $studentSuccessProjects, $studentTotalTime - $studentSuccessTime, $studentFailureProjects];

                $studentSuccess = $studentSuccessTime / $studentTotalTime >= self::SUCCESS_REQUIREMENT;

                if ($studentSuccess) {
                    $groupSuccessCount++;
                    $groupSuccessDetails[] = $studentName;
                } else {
                    $groupFailureCount++;
                    $groupFailureDetails[] = $studentName;
                }

            });

            //Put 'all' at the beginning for easier post processing
            $summaries[$groupName] = array_merge(['all' => [$groupSuccessCount, $groupSuccessDetails, $groupFailureCount, $groupFailureDetails]], $summaries[$groupName]);
            //$summaries[$groupName];

        });

        //Compute projects stats
        //Additionne les pourcentages par projet
        //collect($data)->flatten(2)->groupBy(fn($eval)=>$eval[2])->map(fn($project)=>$project->reduce(fn($carry,$eval2)=>$carry+$eval2[1],0))

        //collect($data)->flatten(2)->groupBy(fn($eval)=>$eval[2])->map(fn($project)=>$project->reduce(fn($carry,$eval2)=>[$carry[0]+$eval2[1],'bob'.$carry[1]],[0,'max']))
        if ($json) {
            if (count($seriesData) > 0) {
                return json_encode([
                    'evaluations' => $seriesData,
                    'summaries' => $summaries,
                    'datesWindow' => [
                        $period->start->format(DateFormat::ECHARTS_FORMAT),
                        $period->end->format(DateFormat::ECHARTS_FORMAT),
                        $startZoom->format(DateFormat::ECHARTS_FORMAT),
                        now()->addDay(5)->format(DateFormat::ECHARTS_FORMAT),
                    ],
                    'groupsCount' => count($seriesData),
                ]);
            }

            return '{}';
        } else {
            return collect($seriesData);
        }

    }

    /**
     * Format is [cin1b][bob][[1.1.2021,55%,...,projectName,clients]]
     *
     * @param  array  $seriesData
     */
    public function buildSeriesData(_IH_WorkerContract_C|\Illuminate\Database\Eloquent\Collection|array $wContracts,
        RequiredTimeUnit $timeUnit,
        bool $fullName = false): array
    {
        $seriesData = [];
        $accumulatedTime = 0;
        $accumulatedSuccessTime = 0;
        $previousWorkerName = '';

        foreach ($wContracts as /* @var $wContract WorkerContract */ $wContract) {
            /* @var $contract Contract */
            $contract = $wContract->contract;

            //Do not use $wContract->alreadyEvaluated because it handles remediation
            //But even with remediation, we keep the old result...
            if ($wContract->evaluation_result !== null && $wContract->getAllocatedTime() > 0) {

                $groupMember = $wContract->groupMember;
                $worker = $groupMember->user;
                $workerName = $fullName ?
                    $worker->firstname.'|'.$worker->lastname :
                    $worker->getFirstnameL();
                //Worker switch, reset accumulators
                //[for perf reasons, all data is fetched with 1 request with all contracts sorted by userid...]
                if ($workerName != $previousWorkerName) {
                    $previousWorkerName = $workerName;
                    $accumulatedTime = 0;
                    $accumulatedSuccessTime = 0;
                }

                $group = $groupMember->group->groupName->name;
                $success = $wContract->isSuccess();
                $project = $contract->jobDefinition->title;

                $date = $wContract->success_date;
                $time = $wContract->getAllocatedTime($timeUnit);

                $successTime = $success ? $time : 0;

                $accumulatedTime += $time;
                $accumulatedSuccessTime += $successTime;

                $formattedDateForECharts = $date->format('Y-m-d h:i');
                $formattedSwissDate = $date->format(SwissFrenchDateFormat::DATE_TIME);
                $percentage = round($accumulatedSuccessTime / $accumulatedTime * 100);

                $clients = $contract->clients->transform(fn ($client) => $client->getFirstnameL())->implode(',');

                $part = '';
                if ($wContract->name != null) {
                    $part = '-'.$wContract->name;
                }
                //Used for excel export when grouping by project... as a single project
                //may have contracts with specific periods OR different "parts", it's better creating
                //a custom ID with all info when we have it
                $projectDefaultTime = $contract->jobDefinition->getAllocatedTime(RequiredTimeUnit::PERIOD);
                $projectSpecific = $project.$part.' ('.$projectDefaultTime.'p';

                $successComment = $wContract->success_comment ?? '';

                //ATTENTION: for echarts series format (as currently used), first 2 infos are X and Y ...
                $seriesData[$group][$workerName][] = [
                    self::PI_DATE => $formattedDateForECharts,
                    self::PI_CURRENT_PERCENTAGE => $percentage,
                    self::PI_SUCCESS_TIME => $successTime,
                    self::PI_TIME => $time,
                    self::PI_ACCUMULATED_SUCCESS_TIME => $accumulatedSuccessTime,
                    self::PI_ACCUMULATED_TIME => $accumulatedTime,
                    self::PI_PROJECT => $project,
                    self::PI_CLIENTS => $clients,
                    self::PI_PROJECT_SPECIFIC => $projectSpecific,
                    self::PI_DATE_SWISS => $formattedSwissDate,
                    self::PI_SUCCESS_COMMENT => $successComment,
                    self::PI_REMEDIATION_STATUS => $wContract->remediation_status,
                ];

                //Idea of evolution for easier data post-processing:
                // $seriesData[]=['group'=>$group,'worker'=>$workerName,'data'=>[$formattedDate, $percentage, $successTime, $time, $totalSuccessTime, $totalTime, $project,$clients]];
            }
        }

        return $seriesData;
    }
}
