<?php

namespace App\DataObjects;

use Illuminate\Support\Collection;

/**
 * Represents all evaluation data for a single student
 */
class StudentEvaluationData
{
    /**
     * @param string $studentName Student identifier (firstname|lastname or firstnameL)
     * @param Collection<EvaluationPoint> $evaluationPoints Collection of evaluation points
     */
    public function __construct(
        public readonly string $studentName,
        public readonly Collection $evaluationPoints,
    ) {}

    /**
     * Get the total success time across all evaluations
     */
    public function getTotalSuccessTime(): float
    {
        return $this->evaluationPoints->sum(fn(EvaluationPoint $point) => $point->successTime);
    }

    /**
     * Get the total time across all evaluations
     */
    public function getTotalTime(): float
    {
        return $this->evaluationPoints->sum(fn(EvaluationPoint $point) => $point->time);
    }

    /**
     * Get the current success percentage
     */
    public function getCurrentPercentage(): int
    {
        $totalTime = $this->getTotalTime();
        if ($totalTime == 0) {
            return 0;
        }
        return (int) round(($this->getTotalSuccessTime() / $totalTime) * 100);
    }

    /**
     * Get all successful projects
     * @return array<string>
     */
    public function getSuccessfulProjects(): array
    {
        return $this->evaluationPoints
            ->filter(fn(EvaluationPoint $point) => $point->isSuccess())
            ->map(fn(EvaluationPoint $point) => $point->project)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Get all failed projects
     * @return array<string>
     */
    public function getFailedProjects(): array
    {
        return $this->evaluationPoints
            ->filter(fn(EvaluationPoint $point) => !$point->isSuccess())
            ->map(fn(EvaluationPoint $point) => $point->project)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Check if the student has overall success (80% or more)
     */
    public function hasOverallSuccess(): bool
    {
        return $this->getCurrentPercentage() >= 80;
    }
}
