<?php

declare(strict_types=1);

namespace Avax\Components\Security\Redaction\System\Flows\RedactLogData;

use Avax\Components\Security\Redaction\System\Capabilities\PolicyEngine\PolicyEngine;

final readonly class RedactLogData
{
    public function __construct(
        private PolicyEngine $policyEngine = new PolicyEngine(),
    ) {}

    /**
     * @param array<string, mixed> $logData
     * @param list<string>         $sensitiveKeys
     *
     * @return array<string, mixed>
     */
    public function execute(array $logData, array $sensitiveKeys = []) : array
    {
        return $this->policyEngine->apply(data: $logData, sensitiveKeys: $sensitiveKeys);
    }
}
