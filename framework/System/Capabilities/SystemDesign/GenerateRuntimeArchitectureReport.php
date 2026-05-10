<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\SystemDesign;

use Avax\Framework\System\Capabilities\SystemDesign\Foundation\ArchitectureFinding;
use Avax\Framework\System\Capabilities\SystemDesign\Foundation\RuntimeArchitectureReport;

final readonly class GenerateRuntimeArchitectureReport
{
    /**
     * Generate an architecture report from runtime inspection.
     *
     * @param list<callable(): list<ArchitectureFinding>> $inspectors
     */
    public function __construct(
        private array $inspectors = [],
    ) {
    }

    public function generate(): RuntimeArchitectureReport
    {
        $findings = [];

        foreach ($this->inspectors as $inspector) {
            $findings = array_merge($findings, $inspector());
        }

        return new RuntimeArchitectureReport(findings: $findings);
    }
}
