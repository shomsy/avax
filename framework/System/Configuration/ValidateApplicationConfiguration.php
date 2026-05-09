<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration;

use Avax\Framework\System\Configuration\Foundation\ApplicationConfiguration;
use Avax\Framework\System\Configuration\Foundation\InvalidConfiguration;

/**
 * ValidateApplicationConfiguration — Validates application configuration values.
 */
final class ValidateApplicationConfiguration
{
    /**
     * @throws InvalidConfiguration
     */
    public function validate(ApplicationConfiguration $config): void
    {
        if ($config->name === '') {
            throw new InvalidConfiguration('Application name cannot be empty.');
        }

        $allowedEnvironments = ['production', 'development', 'testing', 'staging'];
        if (! in_array($config->environment, $allowedEnvironments, true)) {
            throw new InvalidConfiguration("Invalid environment: {$config->environment}. Allowed: " . implode(', ', $allowedEnvironments));
        }

        if ($config->timezone === '') {
            throw new InvalidConfiguration('Timezone cannot be empty.');
        }

        if (! @date_default_timezone_set($config->timezone)) {
            throw new InvalidConfiguration("Invalid timezone: {$config->timezone}.");
        }
    }
}
