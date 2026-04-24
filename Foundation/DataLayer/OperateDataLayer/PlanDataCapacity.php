<?php

declare(strict_types=1);

namespace Avax\DataLayer\OperateDataLayer;

final readonly class PlanDataCapacity
{
    public function __construct(
        private CapacityPlan $plan
    ) {}

    public function describeResponsibility() : string
    {
        return 'plans data capacity including current usage, growth, and scaling triggers.';
    }

    public function plan() : CapacityPlanningResult
    {
        $capacityPlan = $this->plan->planCapacity();

        return new CapacityPlanningResult(
            currentCapacityMb     : $capacityPlan->currentCapacityMb,
            projectedCapacityMb   : $capacityPlan->requiredCapacityMb,
            recommendedShardCount : $capacityPlan->recommendedShardCount,
            scaleTriggerCapacityMb: $capacityPlan->requiredCapacityMb
        );
    }

    public function toMetadata() : array
    {
        return $this->plan->toMetadata();
    }
}

final readonly class CapacityPlanningResult
{
    public function __construct(
        public int $currentCapacityMb,
        public int $projectedCapacityMb,
        public int $recommendedShardCount,
        public int $scaleTriggerCapacityMb
    ) {}
}