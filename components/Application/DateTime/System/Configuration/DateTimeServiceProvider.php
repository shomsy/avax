<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Application\DateTime\System\Capabilities\Timezone\Timezone;
use Avax\Components\Application\DateTime\System\Capabilities\Timezone\UtcTimezone;
use Avax\Components\Application\DateTime\System\PublicSurface\Clock;

/**
 * DateTimeServiceProvider — registers datetime component dependencies.
 */
final class DateTimeServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // UTC timezone — default timezone capability
        $container->singleton(Timezone::class, static fn () : Timezone => new UtcTimezone());

        // Clock — public surface for datetime operations (uses static SystemClock)
        $container->singleton(Clock::class, static fn () : Clock => new Clock());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed — Clock uses static SystemClock internally
    }
}
