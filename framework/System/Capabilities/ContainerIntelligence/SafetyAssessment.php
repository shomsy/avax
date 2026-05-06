<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ContainerIntelligence;

final readonly class SafetyAssessment
{
    /**
     * @param  list<string>  $violations
     */
    public function __construct(
        public bool $safe,
        public array $violations = [],
    ) {
    }
}
