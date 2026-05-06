<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ContainerIntelligence;

use Avax\Components\Application\Container\System\ContainerInterface;

final readonly class ContainerAnalyzer
{
    public function __construct(private ?ContainerInterface $container = null)
    {
    }

    public function assessWorkerSafety(): SafetyAssessment
    {
        return new SafetyAssessment(safe: true, violations: []);
    }

    /**
     * @return list<ScopeViolation>
     */
    public function detectScopeViolations(): array
    {
        return [];
    }

    /**
     * @return list<string>
     */
    public function getServiceDependencies(): array
    {
        return [];
    }

    public function why(string $id): ContainerDependencyExplanation
    {
        return new ContainerDependencyExplanation(
            serviceId: $id,
            scope: 'unknown',
            isShared: false,
            isLazy: false,
            isDeferred: false,
            dependencies: $this->getServiceDependencies(),
            workerSafe: true,
            explanation: sprintf('Service %s is available.', $id),
        );
    }

    /**
     * @return list<string>
     */
    public function whoUses(): array
    {
        return [];
    }

    /**
     * @return list<string>
     */
    public function whatBreaksIf(): array
    {
        return [];
    }
}
