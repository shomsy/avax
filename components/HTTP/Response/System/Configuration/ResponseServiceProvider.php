<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Components\HTTP\Response\System\Capabilities\Status\ResolveStatusReason;
use Avax\Components\HTTP\Response\System\Flows\BuildResponse\BuildResponse;
use Avax\Components\HTTP\Response\System\Flows\BuildResponse\NormalizeResponseBody;
use Avax\Components\HTTP\Response\System\Flows\BuildResponse\NormalizeResponseHeaders;
use Avax\Components\HTTP\Response\System\PublicSurface\Responses;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * ResponseServiceProvider — registers HTTP response component dependencies.
 *
 * Ownership model:
 * - CreateHttpResponse: internal construction capability (single owner of response creation)
 * - Responses: PublicSurface facade, implements PSR-17 ResponseFactoryInterface
 * - ResponseFactoryInterface: bound to Responses (canonical PSR-17 factory for runtime users)
 * - BuildResponse flow: legacy normalization pipeline (deprecated, retained for internal flows)
 */
final class ResponseServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Internal response construction capability — stateless, single owner
        $container->singleton(CreateHttpResponse::class, static fn () : CreateHttpResponse => new CreateHttpResponse());

        // PublicSurface facade — developer-friendly entry point, PSR-17 adapter
        $container->singleton(Responses::class, static fn (ContainerInterface $c) : Responses => new Responses(
            createHttpResponse: $c->get(CreateHttpResponse::class),
        ));

        // PSR-17 ResponseFactoryInterface → canonical Responses facade
        $container->alias(ResponseFactoryInterface::class, Responses::class);

        // Status resolution — stateless
        $container->singleton(ResolveStatusReason::class, static fn () : ResolveStatusReason => new ResolveStatusReason());

        // Normalization capabilities — stateless (legacy pipeline, internal use only)
        $container->singleton(NormalizeResponseBody::class, static fn () : NormalizeResponseBody => new NormalizeResponseBody());
        $container->singleton(NormalizeResponseHeaders::class, static fn () : NormalizeResponseHeaders => new NormalizeResponseHeaders());

        // BuildResponse flow — requires injected normalization capabilities (legacy pipeline)
        $container->singleton(BuildResponse::class, static fn (ContainerInterface $c) : BuildResponse => new BuildResponse(
            normalizeResponseBody   : $c->get(NormalizeResponseBody::class),
            normalizeResponseHeaders: $c->get(NormalizeResponseHeaders::class),
        ));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
