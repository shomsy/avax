<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Diagnostics;

/**
 * Outbound security notification emitted from audit events.
 */
final readonly class SecurityNotification
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public string $name,
        public string $severity,
        public array $context = [],
        public string|null $correlationId = null
    ) {}
}
