<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\HTTP\Client\System\Capabilities\Http\CurlClient;
use Avax\Components\HTTP\Client\System\PublicSurface\HttpClient;

/**
 * HttpClientServiceProvider — registers HTTP client component dependencies.
 *
 * The existing HttpClientProvider factory class provides static convenience methods
 * (forApi, forWebhooks, forFileDownloads) which remain available. This ServiceProvider
 * registers the default HttpClient binding through the container.
 */
final class HttpClientServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // CurlClient — default HTTP transport
        $container->singleton(CurlClient::class, static fn () : CurlClient => new CurlClient());

        // HttpClient — resolves curl client from container
        $container->singleton(HttpClient::class, static fn (ContainerInterface $c) : HttpClient => new HttpClient(
            curlClient: $c->get(CurlClient::class),
        ));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
