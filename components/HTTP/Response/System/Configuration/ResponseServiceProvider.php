<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\HTTP\Response\System\Capabilities\Status\ResolveStatusReason;
use Avax\Components\HTTP\Response\System\Flows\BuildResponse\BuildEmptyResponse;
use Avax\Components\HTTP\Response\System\Flows\BuildResponse\BuildHtmlResponse;
use Avax\Components\HTTP\Response\System\Flows\BuildResponse\BuildJsonResponse;
use Avax\Components\HTTP\Response\System\Flows\BuildResponse\BuildRedirectResponse;
use Avax\Components\HTTP\Response\System\Flows\BuildResponse\BuildResponse;
use Avax\Components\HTTP\Response\System\Flows\BuildResponse\BuildTextResponse;
use Avax\Components\HTTP\Response\System\Flows\BuildResponse\NormalizeResponseBody;
use Avax\Components\HTTP\Response\System\Flows\BuildResponse\NormalizeResponseHeaders;

/**
 * ResponseServiceProvider — registers HTTP response component dependencies.
 */
final class ResponseServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Status resolution — stateless
        $container->singleton(ResolveStatusReason::class, static fn () : ResolveStatusReason => new ResolveStatusReason());

        // Normalization capabilities — stateless
        $container->singleton(NormalizeResponseBody::class, static fn () : NormalizeResponseBody => new NormalizeResponseBody());
        $container->singleton(NormalizeResponseHeaders::class, static fn () : NormalizeResponseHeaders => new NormalizeResponseHeaders());

        // BuildResponse flow — requires injected normalization capabilities
        $container->singleton(BuildResponse::class, static fn (ContainerInterface $c) : BuildResponse => new BuildResponse(
            normalizeResponseBody   : $c->get(NormalizeResponseBody::class),
            normalizeResponseHeaders: $c->get(NormalizeResponseHeaders::class),
        ));

        // Specialized response builders — static-only, no constructor params
        $container->singleton(BuildJsonResponse::class, static fn () : BuildJsonResponse => new BuildJsonResponse());
        $container->singleton(BuildHtmlResponse::class, static fn () : BuildHtmlResponse => new BuildHtmlResponse());
        $container->singleton(BuildTextResponse::class, static fn () : BuildTextResponse => new BuildTextResponse());
        $container->singleton(BuildRedirectResponse::class, static fn () : BuildRedirectResponse => new BuildRedirectResponse());
        $container->singleton(BuildEmptyResponse::class, static fn () : BuildEmptyResponse => new BuildEmptyResponse());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
