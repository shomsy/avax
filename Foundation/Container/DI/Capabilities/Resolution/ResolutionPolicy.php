<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Resolution;

use SensitiveParameter;

/**
 * Resolution policy for the container runtime.
 */
final readonly class ResolutionPolicy
{
    public const string PROFILE_RELAXED = 'relaxed';

    public const string PROFILE_BALANCED = 'balanced';

    public const string PROFILE_STRICT = 'strict';

    public const string FAIL_MODE_OPEN = 'open';

    public const string FAIL_MODE_CLOSED = 'closed';
    public array  $profiles;
    public string $failMode;
    public string $profile;
    public bool   $debug;
    public bool   $strict;

    public function __construct(
        bool|null   $strict = null,
        bool|null   $debug = null,
        string|null $profile = null,
        string|null $failMode = null,
        /** @var array<string, string> */
        array       $profiles = []
    )
    {
        $strict         ??= false;
        $debug          ??= false;
        $profile        ??= self::PROFILE_BALANCED;
        $failMode       ??= self::FAIL_MODE_CLOSED;
        $this->strict   = $strict;
        $this->debug    = $debug;
        $this->profile  = $profile;
        $this->failMode = $failMode;
        $this->profiles = $profiles;
    }

    public function isAllowed(string $abstract) : bool
    {
        if (! $this->strict) {
            return true;
        }

        return class_exists(class: $abstract) || interface_exists(interface: $abstract);
    }

    public function severityFor(#[SensitiveParameter] string $code, string $defaultSeverity) : string
    {
        return match ($this->profile) {
            self::PROFILE_RELAXED => match ($code) {
                'POL-001', 'POL-002', 'POL-003', 'POL-005', 'POL-006', 'POL-009', 'POL-010', 'POL-011', 'POL-012', 'POL-013' => 'warn',
                default                                                                                                      => $defaultSeverity,
            },
            self::PROFILE_STRICT  => match ($code) {
                'POL-006', 'POL-009', 'POL-010', 'POL-011', 'POL-012', 'POL-013' => 'error',
                default                                                          => $defaultSeverity,
            },
            default               => $defaultSeverity,
        };
    }

    public function forEnvironment(string $environment) : self
    {
        $normalized = trim(string: $environment);
        if ($normalized === '' || ! isset($this->profiles[$normalized])) {
            return $this;
        }

        return new self(
            strict  : $this->strict,
            debug   : $this->debug,
            profile : $this->profiles[$normalized],
            failMode: $this->failMode,
            profiles: $this->profiles
        );
    }

    public function shouldFailOn(string $severity) : bool
    {
        return $this->failMode === self::FAIL_MODE_CLOSED
            && strtolower(string: trim(string: $severity)) === 'error';
    }

    public function isFailClosed() : bool
    {
        return $this->failMode === self::FAIL_MODE_CLOSED;
    }
}
