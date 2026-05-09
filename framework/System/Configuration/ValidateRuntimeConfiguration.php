<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration;

use Avax\Framework\System\Configuration\Foundation\InvalidConfiguration;
use Avax\Framework\System\Configuration\Foundation\RuntimeConfiguration;

/**
 * ValidateRuntimeConfiguration — Validates runtime configuration values.
 */
final class ValidateRuntimeConfiguration
{
    /**
     * @throws InvalidConfiguration
     */
    public function validate(RuntimeConfiguration $config): void
    {
        if ($config->host === '') {
            throw new InvalidConfiguration('Runtime host cannot be empty.');
        }

        if ($config->port < 1 || $config->port > 65535) {
            throw new InvalidConfiguration("Runtime port must be between 1 and 65535, got: {$config->port}.");
        }

        if ($config->memoryGuardSoftLimit <= 0) {
            throw new InvalidConfiguration('Memory guard soft limit must be positive.');
        }

        if ($config->memoryGuardHardLimit <= $config->memoryGuardSoftLimit) {
            throw new InvalidConfiguration('Memory guard hard limit must exceed soft limit.');
        }

        if ($config->maxRequests < 0) {
            throw new InvalidConfiguration('Max requests cannot be negative.');
        }

        $allowedRuntimes = ['built-in', 'reactphp'];
        if (! in_array($config->defaultRuntime, $allowedRuntimes, true)) {
            throw new InvalidConfiguration("Invalid default runtime: {$config->defaultRuntime}. Allowed: " . implode(', ', $allowedRuntimes));
        }
    }
}
