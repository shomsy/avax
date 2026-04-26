<?php

declare(strict_types=1);

namespace Avax\DataLayer\OperateDataLayer;

final readonly class VerifyRestorePlan
{
    public function __construct(
        private RestorePlan $plan
    ) {}

    public function describeResponsibility() : string
    {
        return 'verifies restore plan can be executed successfully.';
    }

    public function verify() : RestorePlanVerificationResult
    {
        $checks = [
            'backup_exists'    => ! empty($this->plan->backupLocation),
            'target_available' => ! empty($this->plan->targetEndpoints),
            'location_valid'   => str_starts_with($this->plan->backupLocation, 's3://')
                || str_starts_with($this->plan->backupLocation, '/'),
        ];

        $allPassed = ! in_array(false, $checks, true);

        return new RestorePlanVerificationResult(
            allPassed: $allPassed,
            checks   : $checks
        );
    }

    public function toMetadata() : array
    {
        return $this->plan->toMetadata();
    }
}

final readonly class RestorePlanVerificationResult
{
    public function __construct(
        public bool  $allPassed,
        public array $checks
    ) {}
}