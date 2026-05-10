<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\SystemDesign\Foundation;

final readonly class RuntimeArchitectureReport
{
    public function __construct(
        /** @var list<ArchitectureFinding> $findings */
        public array $findings = [],
        /** @var list<CapacityRecommendation> $recommendations */
        public array $recommendations = [],
        /** @var list<ConsistencyFinding> $consistencyFindings */
        public array $consistencyFindings = [],
        /** @var list<FailureSimulationResult> $simulationResults */
        public array $simulationResults = [],
        public string $summary = '',
    ) {
    }
}
