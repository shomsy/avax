<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Diagnostics;

/**
 * Outbound security notification emitted from audit events.
 */
final readonly class SecurityNotification
{
    /** @var array<string, mixed> */
    public array $context;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public string      $name,
        public string      $severity,
        array|null         $context = null,
        public string|null $correlationId = null
    )
    {
        $context       ??= [];
        $this->context = $context;
    }
}
