<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * ObservabilityServiceProvider — registers observability component dependencies.
 */
final class ObservabilityServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Observability configuration interface — no concrete implementation exists yet.
        // Configuration is assembled at runtime by the application.
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed — Observability uses static value objects
    }
}
