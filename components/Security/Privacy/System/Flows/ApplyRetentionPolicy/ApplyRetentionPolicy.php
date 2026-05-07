<?php

declare(strict_types=1);

namespace Avax\Components\Security\Privacy\System\Flows\ApplyRetentionPolicy;

use Avax\Components\Security\Privacy\System\Capabilities\RetentionPolicyManager\RetentionPolicyManager;

final readonly class ApplyRetentionPolicy
{
    public function __construct(
        private RetentionPolicyManager $policyManager = new RetentionPolicyManager(retentionDays: 365),
    ) {}

    /**
     * @param array<string, mixed> $records
     *
     * @return array{expired_count:int,active_count:int,expired:list<array<string,mixed>>,active:list<array<string,mixed>>}
     */
    public function execute(array $records, string $dateField = 'created_at') : array
    {
        $result = $this->policyManager->apply(records: $records, dateField: $dateField);

        return [
            'expired_count' => count(value: $result['expired']),
            'active_count'  => count(value: $result['active']),
            'expired'       => $result['expired'],
            'active'        => $result['active'],
        ];
    }
}
