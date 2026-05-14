<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Framework\System\Capabilities\RuntimeSafety\ResetVerification\ResetVerifier;
use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafety;
use Avax\Framework\System\Capabilities\RuntimeSafety\StateLeakDetection\StateLeakDetector;
use Avax\Framework\System\Capabilities\RuntimeSafety\StaticStateScanner;
use Avax\Framework\System\Configuration\LoadConfiguration\ConfigurationRepository;

/**
 * FrameworkServiceProvider — registers core framework-level dependencies.
 *
 * This provider handles framework assembly that is not owned by any single component:
 * runtime safety, configuration repository, and framework-level capabilities.
 */
final class FrameworkServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Configuration repository — central config store
        $container->singleton(ConfigurationRepository::class, static fn () : ConfigurationRepository => new ConfigurationRepository());

        // Runtime safety capabilities
        $container->singleton(StateLeakDetector::class, static fn () : StateLeakDetector => new StateLeakDetector());
        $container->singleton(StaticStateScanner::class, static fn () : StaticStateScanner => new StaticStateScanner());
        $container->singleton(ResetVerifier::class, static fn () : ResetVerifier => new ResetVerifier());

        // RuntimeSafety coordinator — requires all safety capabilities
        $container->singleton(RuntimeSafety::class, static fn (ContainerInterface $c) : RuntimeSafety => new RuntimeSafety(
            stateLeakDetector : $c->get(StateLeakDetector::class),
            staticStateScanner: $c->get(StaticStateScanner::class),
            resetVerifier     : $c->get(ResetVerifier::class),
        ));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
