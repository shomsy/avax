<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Dependencies\Resolution;

/**
 * Resolution policy for the container runtime.
 */
final readonly class ResolutionPolicy
{
    public const PROFILE_RELAXED = 'relaxed';

    public const PROFILE_BALANCED = 'balanced';

    public const PROFILE_STRICT = 'strict';

    public function __construct(
        public bool $strict = false,
        public bool $debug = false,
        public string $profile = self::PROFILE_BALANCED
    ) {}

    public function isAllowed(string $abstract) : bool
    {
        if (! $this->strict) {
            return true;
        }

        return class_exists($abstract) || interface_exists($abstract);
    }

    public function severityFor(string $code, string $defaultSeverity) : string
    {
        return match ($this->profile) {
            self::PROFILE_RELAXED => match ($code) {
                'POL-001', 'POL-002', 'POL-003', 'POL-005', 'POL-006', 'POL-009', 'POL-010', 'POL-011', 'POL-012', 'POL-013' => 'warn',
                default => $defaultSeverity,
            },
            self::PROFILE_STRICT => match ($code) {
                'POL-006', 'POL-009', 'POL-010', 'POL-011', 'POL-012', 'POL-013' => 'error',
                default => $defaultSeverity,
            },
            default => $defaultSeverity,
        };
    }
}
