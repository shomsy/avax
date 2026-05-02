<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ConfigValidation;

/**
 * Represents a config validation violation.
 */
final readonly class ConfigSchemaViolation
{
    public const string SEVERITY_ERROR = 'error';

    public const string SEVERITY_WARNING = 'warning';

    public function __construct(
        public string $severity,
        public string $key,
        public string $message,
        public ?string $remediation = null,
    ) {
    }

    public function isError(): bool
    {
        return $this->severity === self::SEVERITY_ERROR;
    }
}
