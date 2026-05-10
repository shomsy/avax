<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\SystemDesign\Foundation;

final readonly class FailureSimulationResult
{
    public function __construct(
        public string $scenario,
        public string $status,
        /** @var list<string> $findings */
        public array $findings = [],
        /** @var list<string> $recommendations */
        public array $recommendations = [],
    ) {
    }
}
