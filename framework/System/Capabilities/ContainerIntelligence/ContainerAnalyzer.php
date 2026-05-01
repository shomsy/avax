<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ContainerIntelligence;

use Avax\Components\Application\Container\System\ContainerInterface;

final readonly class ContainerAnalyzer
{
    public function __construct(
        private ContainerInterface $container,
    ) {
    }

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
    public function getServiceDependencies(string $id): array
    {
        return [];
    }

    public function why(string $id): string
    {
        return "Service {$id} is available.";
    }

    /**
     * @return list<string>
     */
    public function whoUses(string $id): array
    {
        return [];
    }

    /**
     * @return list<string>
     */
    public function whatBreaksIf(string $id): array
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
