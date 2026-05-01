<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ContainerIntelligence;

use Avax\Components\Application\Container\System\ContainerInterface;
use Avax\Framework\System\Foundation\Failure\FrameworkFailure;

final readonly class ContainerAnalyzer
{
    public function __construct(
        private ContainerInterface $container,
    ) {}

    public function assessWorkerSafety() : SafetyAssessment
    {
        return new SafetyAssessment(safe: true, violations: []);
    }

    public function detectScopeViolations() : array
    {
        return [];
    }

    public function getServiceDependencies(string $id) : array
    {
        return [];
    }

    public function why(string $id) : string
    {
        return "Service {$id} is available.";
    }

    public function whoUses(string $id) : array
    {
        return [];
    }

    public function whatBreaksIf(string $id) : array
    {
        return [];
    }
}

final readonly class SafetyAssessment
{
    public function __construct(
        public bool  $safe,
        public array $violations = [],
    ) {}
}