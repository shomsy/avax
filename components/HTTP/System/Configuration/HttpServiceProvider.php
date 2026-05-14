<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Avax\Components\HTTP\System\Capabilities\Kernel\AppKernel;
use Avax\Components\HTTP\System\Capabilities\Kernel\BootHttpKernel;
use Avax\Components\HTTP\System\Capabilities\Kernel\HttpKernel;
use Avax\Components\HTTP\System\Capabilities\Kernel\TerminateHttpKernel;
use Avax\Components\HTTP\System\Capabilities\MiddlewarePipeline\MiddlewarePipeline;
use Avax\Components\HTTP\System\Capabilities\MiddlewarePipeline\MiddlewareRegistry;
use Avax\Components\HTTP\System\Capabilities\ResponseBuilding\ResponseFactory;
use Avax\Components\HTTP\System\PublicSurface\Http;
use Avax\Components\HTTP\System\PublicSurface\HttpInterface;

/**
 * HttpServiceProvider — registers HTTP system-level component dependencies.
 */
final class HttpServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Middleware pipeline and registry — stateful
        $container->singleton(MiddlewarePipeline::class, static fn () : MiddlewarePipeline => new MiddlewarePipeline());
        $container->singleton(MiddlewareRegistry::class, static fn () : MiddlewareRegistry => new MiddlewareRegistry());

        // Response factory — stateless
        $container->singleton(ResponseFactory::class, static fn () : ResponseFactory => new ResponseFactory());

        // HTTP kernels — stateless
        $container->singleton(BootHttpKernel::class, static fn () : BootHttpKernel => new BootHttpKernel());
        $container->singleton(TerminateHttpKernel::class, static fn () : TerminateHttpKernel => new TerminateHttpKernel());

        // HttpKernel — requires router, boot, and terminate kernels
        $container->singleton(HttpKernel::class, static fn (ContainerInterface $c) : HttpKernel => new HttpKernel(
            router           : $c->get(RouterInterface::class),
            bootHttpKernel   : $c->get(BootHttpKernel::class),
            terminateHttpKernel: $c->get(TerminateHttpKernel::class),
        ));

        // AppKernel — requires HttpKernel
        $container->singleton(AppKernel::class, static fn (ContainerInterface $c) : AppKernel => new AppKernel(
            httpKernel: $c->get(HttpKernel::class),
        ));

        // Http facade — requires router and middleware pipeline
        $container->singleton(HttpInterface::class, static fn (ContainerInterface $c) : Http => new Http(
            router            : $c->get(RouterInterface::class),
            middlewarePipeline: $c->get(MiddlewarePipeline::class),
        ));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
