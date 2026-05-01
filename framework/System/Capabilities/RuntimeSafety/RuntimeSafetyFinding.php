<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeSafety;

/**
 * Represents a single runtime safety finding.
 */
final readonly class RuntimeSafetyFinding
{
    public const SEVERITY_CRITICAL = 'critical';

    public const SEVERITY_WARNING = 'warning';

    public const SEVERITY_INFO = 'info';

    public function __construct(
        public string $category,
        public string $severity,
        public string $component,
        public string $message,
        public ?string $remediation = null,
        public ?string $location = null,
    ) {}

    public function isCritical() : bool
    {
        return $this->severity === self::SEVERITY_CRITICAL;
    }

    public function isWarning() : bool
    {
        return $this->severity === self::SEVERITY_WARNING;
    }
}
