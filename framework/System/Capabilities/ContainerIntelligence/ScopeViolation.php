<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ContainerIntelligence;

/**
 * Represents a scope violation in the container.
 */
final readonly class ScopeViolation
{
    public function __construct(
        public string $service,
        public string $dependency,
        public string $message,
    ) {
    }
}
