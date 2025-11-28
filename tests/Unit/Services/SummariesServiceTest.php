<?php

namespace Tests\Unit\Services;

use App\Constants\RemediationStatus;
use App\DataObjects\EvaluationPoint;
use App\Enums\RequiredTimeUnit;
use App\Exports\EvaluationResult;
use App\Models\AcademicPeriod;
use App\Services\SummariesService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SummariesServiceTest extends TestCase
{
    private SummariesService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterApplicationCreated(function () {
            // Seed the academic period that the tests need
            AcademicPeriod::create([
                'start' => today()->subWeek(),
                'end' => today()->addWeek(),
            ]);
        });

        $this->service = new SummariesService();
    }

    public function test_buildSeriesData_returns_typed_evaluation_points(): void
    {
        // Arrange
        $clientAndJob = $this->createClientAndJob(1);
        $workerContract = $clientAndJob['workerContracts'][0];
        $student = $workerContract->groupMember->user;

        $workerContract->evaluate(EvaluationResult::ACQUIS, 'Good work');

        // Act
        $result = $this->service->buildSeriesData(
            collect([$workerContract]),
            RequiredTimeUnit::PERIOD,
            false
        );

        // Assert
        $this->assertIsArray($result);
        $groupName = $workerContract->groupMember->group->groupName->name;
        $this->assertArrayHasKey($groupName, $result);

        $studentData = $result[$groupName][$student->getFirstnameL()];
        $this->assertInstanceOf(Collection::class, $studentData);
        $this->assertCount(1, $studentData);

        $evaluationPoint = $studentData->first();
        $this->assertInstanceOf(EvaluationPoint::class, $evaluationPoint);
        $this->assertEquals($clientAndJob['job']->title, $evaluationPoint->project);
        $this->assertEquals(EvaluationResult::ACQUIS, $evaluationPoint->evaluationResult);
        $this->assertEquals('Good work', $evaluationPoint->successComment);
    }

    public function test_buildSeriesData_handles_all_evaluation_results(): void
    {
        // Arrange
        $clientAndJob = $this->createClientAndJob(4);
        $workerContracts = $clientAndJob['workerContracts'];

        $evaluationResults = [
            EvaluationResult::NON_ACQUIS,
            EvaluationResult::PARTIELLEMENT_ACQUIS,
            EvaluationResult::ACQUIS,
            EvaluationResult::LARGEMENT_ACQUIS,
        ];

        foreach ($workerContracts as $index => $wc) {
            $wc->evaluate($evaluationResults[$index]);
        }

        $student = $workerContracts[0]->groupMember->user;
        $groupName = $workerContracts[0]->groupMember->group->groupName->name;

        // Act
        $result = $this->service->buildSeriesData(collect($workerContracts), RequiredTimeUnit::PERIOD);

        // Assert
        foreach ($workerContracts as $index => $wc) {
            /* @var $wc \App\Models\WorkerContract */
            $studentData = $result[$groupName][$wc->groupMember->user->getFirstnameL()];
            $this->assertCount(1, $studentData);

            $evaluationPoints = $studentData->values();
            $this->assertEquals($evaluationResults[$index], $evaluationPoints[0]->evaluationResult);

        }

    }

    public function test_buildSeriesData_calculates_success_time_correctly(): void
    {
        // Arrange
        // Create client and job first to ensure academic period exists
        $clientAndJob = $this->createClientAndJob(3);
        $workerContracts = $clientAndJob['workerContracts'];

        // Evaluate: success, failure, success
        $workerContracts[0]->evaluate(EvaluationResult::ACQUIS);
        $workerContracts[1]->evaluate(EvaluationResult::NON_ACQUIS);
        $workerContracts[2]->evaluate(EvaluationResult::LARGEMENT_ACQUIS);

        $groupName = $workerContracts[0]->groupMember->group->groupName->name;

        // Act
        $result = $this->service->buildSeriesData(collect($workerContracts), RequiredTimeUnit::PERIOD);

        // Assert - each worker contract is for a different student
        foreach ($workerContracts as $index => $wc) {
            /* @var $wc \App\Models\WorkerContract */
            $studentData = $result[$groupName][$wc->groupMember->user->getFirstnameL()];
            $this->assertCount(1, $studentData);

            $point = $studentData->first();
            $time = $wc->allocated_time;

            // Each student has only 1 evaluation, so accumulated = current
            $this->assertEquals($time, $point->accumulatedTime);

            // Check success time based on evaluation result
            if ($index === 0) { // ACQUIS
                $this->assertEquals($time, $point->successTime);
                $this->assertEquals($time, $point->accumulatedSuccessTime);
                $this->assertEquals(100, $point->currentPercentage);
            } elseif ($index === 1) { // NON_ACQUIS
                $this->assertEquals(0, $point->successTime);
                $this->assertEquals(0, $point->accumulatedSuccessTime);
                $this->assertEquals(0, $point->currentPercentage);
            } elseif ($index === 2) { // LARGEMENT_ACQUIS
                $this->assertEquals($time, $point->successTime);
                $this->assertEquals($time, $point->accumulatedSuccessTime);
                $this->assertEquals(100, $point->currentPercentage);
            }
        }
    }

    public function test_buildSeriesData_calculates_percentage_correctly(): void
    {
        // Arrange
        $clientAndJob = $this->createClientAndJob(2);
        $workerContracts = $clientAndJob['workerContracts'];

        // Set specific allocated times
        $workerContracts[0]->allocated_time = 80;
        $workerContracts[0]->save();
        $workerContracts[1]->allocated_time = 20;
        $workerContracts[1]->save();

        $workerContracts[0]->evaluate(EvaluationResult::ACQUIS);
        $workerContracts[1]->evaluate(EvaluationResult::NON_ACQUIS);

        $groupName = $workerContracts[0]->groupMember->group->groupName->name;

        // Act
        $result = $this->service->buildSeriesData(collect($workerContracts), RequiredTimeUnit::PERIOD);

        // Assert - each worker contract is for a different student
        // Student 1: 80p success -> 80/80 = 100%
        $student1Data = $result[$groupName][$workerContracts[0]->groupMember->user->getFirstnameL()];
        $this->assertEquals(100, $student1Data->first()->currentPercentage);

        // Student 2: 20p failure -> 0/20 = 0%
        $student2Data = $result[$groupName][$workerContracts[1]->groupMember->user->getFirstnameL()];
        $this->assertEquals(0, $student2Data->first()->currentPercentage);
    }

    public function test_buildSeriesData_handles_remediation_status(): void
    {
        // Arrange
        $clientAndJob = $this->createClientAndJob(1);
        $workerContract = $clientAndJob['workerContracts'][0];

        $workerContract->evaluate(EvaluationResult::NON_ACQUIS);
        $workerContract->remediation_status = RemediationStatus::EVALUATED;
        $workerContract->save();

        $student = $workerContract->groupMember->user;
        $groupName = $workerContract->groupMember->group->groupName->name;

        // Act
        $result = $this->service->buildSeriesData(collect([$workerContract]), RequiredTimeUnit::PERIOD);

        // Assert
        $studentData = $result[$groupName][$student->getFirstnameL()];
        $point = $studentData->first();

        $this->assertEquals(RemediationStatus::EVALUATED, $point->remediationStatus);
    }

    public function test_buildSeriesData_skips_unevaluated_contracts(): void
    {
        // Arrange
        $clientAndJob = $this->createClientAndJob(1);
        $workerContract = $clientAndJob['workerContracts'][0];

        // Don't evaluate the contract - leave it as null

        // Act
        $result = $this->service->buildSeriesData(collect([$workerContract]), RequiredTimeUnit::PERIOD);

        // Assert
        $this->assertEmpty($result);
    }

    public function test_buildSeriesData_uses_full_name_when_requested(): void
    {
        // Arrange
        $clientAndJob = $this->createClientAndJob(1);
        $workerContract = $clientAndJob['workerContracts'][0];
        $student = $workerContract->groupMember->user;

        $workerContract->evaluate(EvaluationResult::ACQUIS);

        $groupName = $workerContract->groupMember->group->groupName->name;

        // Act
        $result = $this->service->buildSeriesData(
            collect([$workerContract]),
            RequiredTimeUnit::PERIOD,
            true // fullName = true
        );

        // Assert
        $this->assertArrayHasKey($groupName, $result);
        $expectedKey = $student->firstname . '|' . $student->lastname;
        $this->assertArrayHasKey($expectedKey, $result[$groupName]);
    }

    public function test_evaluation_point_isSuccess_method(): void
    {
        // Arrange
        $clientAndJob = $this->createClientAndJob(4);
        $workerContracts = $clientAndJob['workerContracts'];

        $evaluations = [
            ['result' => EvaluationResult::NON_ACQUIS, 'expectedSuccess' => false],
            ['result' => EvaluationResult::PARTIELLEMENT_ACQUIS, 'expectedSuccess' => false],
            ['result' => EvaluationResult::ACQUIS, 'expectedSuccess' => true],
            ['result' => EvaluationResult::LARGEMENT_ACQUIS, 'expectedSuccess' => true],
        ];

        foreach ($workerContracts as $index => $wc) {
            $wc->evaluate($evaluations[$index]['result']);
        }

        $groupName = $workerContracts[0]->groupMember->group->groupName->name;

        // Act
        $result = $this->service->buildSeriesData(collect($workerContracts), RequiredTimeUnit::PERIOD);

        // Assert - each worker contract is for a different student
        foreach ($workerContracts as $index => $wc) {
            /* @var $wc \App\Models\WorkerContract */
            $studentData = $result[$groupName][$wc->groupMember->user->getFirstnameL()];
            $point = $studentData->first();

            $this->assertEquals(
                $evaluations[$index]['expectedSuccess'],
                $point->isSuccess(),
                "EvaluationResult {$evaluations[$index]['result']->value} should " .
                ($evaluations[$index]['expectedSuccess'] ? 'be' : 'not be') . ' a success'
            );
        }
    }

    public function test_buildSeriesData_success_time_only_counts_for_a_and_la(): void
    {
        // Arrange
        $clientAndJob = $this->createClientAndJob(4);
        $workerContracts = $clientAndJob['workerContracts'];

        // All contracts have same allocated time
        foreach ($workerContracts as $wc) {
            $wc->allocated_time = 10;
            $wc->save();
        }

        // Different evaluation results
        $workerContracts[0]->evaluate(EvaluationResult::NON_ACQUIS);
        $workerContracts[1]->evaluate(EvaluationResult::PARTIELLEMENT_ACQUIS);
        $workerContracts[2]->evaluate(EvaluationResult::ACQUIS);
        $workerContracts[3]->evaluate(EvaluationResult::LARGEMENT_ACQUIS);

        $groupName = $workerContracts[0]->groupMember->group->groupName->name;

        // Act
        $result = $this->service->buildSeriesData(collect($workerContracts), RequiredTimeUnit::PERIOD);

        // Assert - each worker contract is for a different student
        // Student 1: NA should not count as success time
        $student1Data = $result[$groupName][$workerContracts[0]->groupMember->user->getFirstnameL()];
        $this->assertEquals(0, $student1Data->first()->successTime);
        $this->assertEquals(0, $student1Data->first()->accumulatedSuccessTime);

        // Student 2: PA should not count as success time
        $student2Data = $result[$groupName][$workerContracts[1]->groupMember->user->getFirstnameL()];
        $this->assertEquals(0, $student2Data->first()->successTime);
        $this->assertEquals(0, $student2Data->first()->accumulatedSuccessTime);

        // Student 3: A should count as success time
        $student3Data = $result[$groupName][$workerContracts[2]->groupMember->user->getFirstnameL()];
        $this->assertEquals(10, $student3Data->first()->successTime);
        $this->assertEquals(10, $student3Data->first()->accumulatedSuccessTime);

        // Student 4: LA should count as success time
        $student4Data = $result[$groupName][$workerContracts[3]->groupMember->user->getFirstnameL()];
        $this->assertEquals(10, $student4Data->first()->successTime);
        $this->assertEquals(10, $student4Data->first()->accumulatedSuccessTime);
    }
}
