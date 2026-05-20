<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Context\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\HTTP\Context\System\Capabilities\Globals\GlobalsProviderInterface;
use Avax\Components\HTTP\Context\System\Capabilities\Globals\PhpGlobalsProvider;
use Avax\Components\HTTP\Context\System\PublicSurface\HttpContext;
use Avax\Components\HTTP\Context\System\PublicSurface\HttpContextInterface;

/**
 * HttpContextServiceProvider — registers HTTP Context component dependencies.
 *
 * Registers the globals provider and HTTP context singleton backed by either
 * a PSR-7 request or PHP globals.
 */
final class HttpContextServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // GlobalsProviderInterface — PhpGlobalsProvider default, no dependencies
        $container->singleton(GlobalsProviderInterface::class, static fn () : GlobalsProviderInterface => new PhpGlobalsProvider());

        // HttpContextInterface — requires GlobalsProviderInterface
        $container->singleton(HttpContextInterface::class, static fn (ContainerInterface $c) : HttpContext => new HttpContext(
            serverRequest  : null,
            globalsProvider: $c->get(GlobalsProviderInterface::class),
        ));

        // Concrete class alias to the interface registration
        $container->singleton(HttpContext::class, static fn (ContainerInterface $c) : HttpContext => $c->get(HttpContextInterface::class));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
