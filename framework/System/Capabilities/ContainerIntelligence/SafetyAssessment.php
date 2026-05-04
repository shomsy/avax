<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ContainerIntelligence;

use Avax\Components\Application\Container\System\ContainerInterface;

final readonly class SafetyAssessment
{
    /**
     * @param list<string> $violations
     */
    public function __construct(
        public bool  $safe,
        public array $violations = [],
    )
    {
    }
}
