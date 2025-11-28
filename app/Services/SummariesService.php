<?php

namespace App\Services;

use App\Constants\RoleName;
use App\DataObjects\EvaluationPoint;
use App\DateFormat;
use App\Enums\RequiredTimeUnit;
use App\Exports\EvaluationResult;
use App\Models\AcademicPeriod;
use App\Models\Contract;
use App\Models\User;
use App\Models\WorkerContract;
use App\SwissFrenchDateFormat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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

                // $studentData is now a Collection<EvaluationPoint>
                [$studentSuccessTime,$studentTotalTime,$studentSuccessProjects, $studentFailureProjects] = $studentData->reduceSpread(
                    fn (int $totalSuccessTime, int $totalTime, array $studentSuccessProjects, array $studentFailureProjects, EvaluationPoint $point) => [
                        $totalSuccessTime + $point->successTime,
                        $totalTime + $point->time,
                        $point->successTime == 0 ? $studentSuccessProjects : array_merge($studentSuccessProjects, [$point->project]),
                        $point->successTime > 0 ? $studentFailureProjects : array_merge($studentFailureProjects, [$point->project]),
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
                // Convert EvaluationPoint objects to arrays for JSON serialization
                $evaluationsForJson = [];
                foreach ($seriesData as $group => $students) {
                    foreach ($students as $studentName => $evaluationPoints) {
                        $evaluationsForJson[$group][$studentName] = $evaluationPoints->map(
                            fn(EvaluationPoint $point) => $point->toArray()
                        )->all();
                    }
                }

                return json_encode([
                    'evaluations' => $evaluationsForJson,
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
     * Build series data with typed objects
     * Returns: [groupName => [studentName => Collection<EvaluationPoint>]]
     *
     * @return array<string, array<string, Collection<EvaluationPoint>>>
     */
    public function buildSeriesData(WorkerContract|Collection|array $wContracts,
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
                $evaluationResult = EvaluationResult::from($wContract->evaluation_result);
                $success = $evaluationResult->isSuccess();
                $project = $contract->jobDefinition->title;

                $date = $wContract->success_date;
                $time = $wContract->getAllocatedTime($timeUnit);

                $successTime = $success ? $time : 0;

                $accumulatedTime += $time;
                $accumulatedSuccessTime += $successTime;

                $formattedDateForECharts = $date->format('Y-m-d h:i');
                $formattedSwissDate = $date->format(SwissFrenchDateFormat::DATE_TIME);
                $percentage = (int) round($accumulatedSuccessTime / $accumulatedTime * 100);

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

                $evaluationPoint = new EvaluationPoint(
                    dateFormatted: $formattedDateForECharts,
                    currentPercentage: $percentage,
                    successTime: $successTime,
                    time: $time,
                    accumulatedSuccessTime: $accumulatedSuccessTime,
                    accumulatedTime: $accumulatedTime,
                    project: $project,
                    clients: $clients,
                    projectSpecific: $projectSpecific,
                    dateSwiss: $formattedSwissDate,
                    successComment: $successComment,
                    remediationStatus: $wContract->remediation_status,
                    evaluationResult: $evaluationResult,
                );

                if (!isset($seriesData[$group][$workerName])) {
                    $seriesData[$group][$workerName] = collect();
                }
                $seriesData[$group][$workerName]->push($evaluationPoint);
            }
        }

        return $seriesData;
    }

    /**
     * Build series data in legacy array format (for backwards compatibility)
     * @deprecated Use buildSeriesData() instead which returns typed objects
     */
    public function buildSeriesDataLegacy(WorkerContract|Collection|array $wContracts,
        RequiredTimeUnit $timeUnit,
        bool $fullName = false): array
    {
        $typedData = $this->buildSeriesData($wContracts, $timeUnit, $fullName);

        // Convert typed data back to legacy array format
        $legacyData = [];
        foreach ($typedData as $group => $students) {
            foreach ($students as $studentName => $evaluationPoints) {
                $legacyData[$group][$studentName] = $evaluationPoints->map(
                    fn(EvaluationPoint $point) => $point->toArray()
                )->all();
            }
        }

        return $legacyData;
    }
}
