<?php

declare(strict_types=1);

namespace Avax\Components\Security\Redaction\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Security\Redaction\System\Capabilities\HealthCheck\CheckRedactionHealth;
use Avax\Components\Security\Redaction\System\Capabilities\PatternMatcher\PatternMatcher;
use Avax\Components\Security\Redaction\System\Capabilities\RedactionEngine\RedactionEngine;

/**
 * RedactionServiceProvider — registers redaction component dependencies.
 */
final class RedactionServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Pattern matcher — sensitive data detection
        $container->singleton(PatternMatcher::class, static fn () : PatternMatcher => new PatternMatcher());

        // Redaction engine — pattern-based redaction (takes $mask, not PatternMatcher)
        $container->singleton(RedactionEngine::class, static fn () : RedactionEngine => new RedactionEngine());

        // Health check
        $container->singleton(CheckRedactionHealth::class, static fn () : CheckRedactionHealth => new CheckRedactionHealth());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
