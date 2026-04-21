<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Diagnostics;

/**
 * Outbound security notification emitted from audit events.
 */
final readonly class SecurityNotification
{
    public string|null $correlationId;
    /** @var array<string, mixed> */
    public array       $context;
    public string      $severity;
    public string      $name;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        string      $name,
        string      $severity,
        array|null  $context = null,
        string|null $correlationId = null
    )
    {
        $context             ??= [];
        $this->name          = $name;
        $this->severity      = $severity;
        $this->context       = $context;
        $this->correlationId = $correlationId;
    }
}
