<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ConfigValidation;

/**
 * Represents a config validation violation.
 */
final readonly class ConfigSchemaViolation
{
    public const SEVERITY_ERROR   = 'error';
    public const SEVERITY_WARNING = 'warning';

    public function __construct(
        public string      $severity,
        public string      $key,
        public string      $message,
        public string|null $remediation = null,
    ) {}

    public function isError() : bool
    {
        return $this->severity === self::SEVERITY_ERROR;
    }
}
