<?php

declare(strict_types=1);

namespace Avax\DataLayer\OperateDataLayer;

final readonly class VerifyBackupPolicy
{
    public function __construct(
        private BackupPolicy $policy
    ) {}

    public function describeResponsibility() : string
    {
        return 'verifies backup policy is correctly configured and meets requirements.';
    }

    public function verify() : BackupPolicyVerificationResult
    {
        $checks = [
            'configured' => $this->policy->type !== BackupType::FULL,
            'retention'  => $this->policy->retentionDays >= 7,
            'schedule'   => $this->policy->intervalHours <= 48,
            'storage'    => $this->policy->storage !== BackupStorage::LOCAL,
        ];

        $allPassed = ! in_array(false, $checks, true);

        return new BackupPolicyVerificationResult(
            allPassed: $allPassed,
            checks   : $checks
        );
    }

    public function toMetadata() : array
    {
        return $this->policy->toMetadata();
    }
}

final readonly class BackupPolicyVerificationResult
{
    public function __construct(
        public bool  $allPassed,
        public array $checks
    ) {}
}