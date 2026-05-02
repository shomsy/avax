<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ContainerIntelligence;

use Avax\Components\Application\Container\System\ContainerInterface;

final readonly class ContainerAnalyzer
{
    public function assessWorkerSafety(): SafetyAssessment
    {
        return new SafetyAssessment(safe: true, violations: []);
    }

    /**
     * @return list<string>
     */
    public function detectScopeViolations(): array
    {
        return [];
    }

    /**
     * @return list<string>
     */
    public function getServiceDependencies() : array
    {
        return [];
    }

    public function why(string $id): string
    {
        return sprintf('Service %s is available.', $id);
    }

    /**
     * @return list<string>
     */
    public function whoUses() : array
    {
        return [];
    }

    /**
     * @return list<string>
     */
    public function whatBreaksIf() : array
    {
        return [];
    }
}

final readonly class SafetyAssessment
{
    /**
     * @param list<string> $violations
     */
    public function __construct(
        public bool $safe,
        public array $violations = [],
    ) {
    }
}
