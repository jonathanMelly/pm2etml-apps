<?php

namespace App\DataObjects;

use App\Constants\RemediationStatus;
use App\Exports\EvaluationResult;

/**
 * Represents a single evaluation point for a worker contract
 */
class EvaluationPoint
{
    public function __construct(
        public readonly string $dateFormatted,
        public readonly int $currentPercentage,
        public readonly float $successTime,
        public readonly float $time,
        public readonly float $accumulatedSuccessTime,
        public readonly float $accumulatedTime,
        public readonly string $project,
        public readonly string $clients,
        public readonly string $projectSpecific,
        public readonly string $dateSwiss,
        public readonly string $successComment,
        public readonly string $remediationStatus,
        public readonly EvaluationResult $evaluationResult,
    ) {}

    /**
     * Convert to array format for backwards compatibility with existing code
     * @deprecated Use typed properties instead
     */
    public function toArray(): array
    {
        return [
            0 => $this->dateFormatted,
            1 => $this->currentPercentage,
            2 => $this->successTime,
            3 => $this->time,
            4 => $this->accumulatedSuccessTime,
            5 => $this->accumulatedTime,
            6 => $this->project,
            7 => $this->clients,
            8 => $this->projectSpecific,
            9 => $this->dateSwiss,
            10 => $this->successComment,
            11 => $this->remediationStatus,
        ];
    }

    /**
     * Check if this evaluation was successful (a or la)
     */
    public function isSuccess(): bool
    {
        return $this->evaluationResult->isSuccess();
    }
}
