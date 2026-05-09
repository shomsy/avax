<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\Capacity\Availability;

/**
 * Service Level Objective model.
 *
 * @experimental V3 labs
 */
final readonly class Slo
{
    public function __construct(
        public float $percentage,
    ) {}

    /**
     * Allowed downtime per year in minutes.
     */
    public function yearlyDowntimeMinutes() : float
    {
        $minutesInYear  = 365.25 * 24 * 60;
        $unavailability = 1 - ($this->percentage / 100);

        return $minutesInYear * $unavailability;
    }

    /**
     * Human-readable downtime budget.
     */
    public function monthlyDowntimeHumanReadable() : string
    {
        $minutes = $this->monthlyDowntimeMinutes();

        if ($minutes < 1) {
            return round($minutes * 60, 1) . ' seconds/month';
        }

        if ($minutes < 60) {
            return round($minutes, 2) . ' minutes/month';
        }

        return round($minutes / 60, 2) . ' hours/month';
    }

    /**
     * Allowed downtime per month in minutes.
     */
    public function monthlyDowntimeMinutes() : float
    {
        $minutesInMonth = 30.44 * 24 * 60; // average month
        $unavailability = 1 - ($this->percentage / 100);

        return $minutesInMonth * $unavailability;
    }

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->percentage <= 0 || $this->percentage > 100) {
            $errors[] = 'SLO must be between 0 and 100.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}
