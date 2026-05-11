<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeSafety;

/**
 * Represents a single runtime safety finding.
 */
final readonly class RuntimeSafetyFinding
{
    public const string SEVERITY_CRITICAL = 'critical';

    public const string SEVERITY_WARNING = 'warning';

    public const string SEVERITY_INFO = 'info';

    public function __construct(
        public string $category,
        public string $severity,
        public string $component,
        public string $message,
        public string|null $remediation = null,
        public string|null $location = null,
    ) {
    }

    public function isCritical(): bool
    {
        return $this->severity === self::SEVERITY_CRITICAL;
    }

    public function isWarning(): bool
    {
        return $this->severity === self::SEVERITY_WARNING;
    }
}
