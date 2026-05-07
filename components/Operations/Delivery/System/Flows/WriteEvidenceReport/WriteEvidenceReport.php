<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Delivery\System\Flows\WriteEvidenceReport;

final readonly class WriteEvidenceReport
{
    /**
     * @param array<string, mixed> $evidence
     *
     * @return array{timestamp: int, evidence: array<string, mixed>}
     */
    public function write(array $evidence) : array
    {
        return [
            'timestamp' => time(),
            'evidence'  => $evidence,
        ];
    }
}
