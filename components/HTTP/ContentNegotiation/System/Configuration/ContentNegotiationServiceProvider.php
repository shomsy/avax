<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\HTTP\ContentNegotiation\System\PublicSurface\ContentFormatter;
use Avax\Components\HTTP\ContentNegotiation\System\PublicSurface\CsvFormatter;
use Avax\Components\HTTP\ContentNegotiation\System\PublicSurface\JsonFormatter;
use Avax\Components\HTTP\ContentNegotiation\System\PublicSurface\XmlFormatter;

/**
 * ContentNegotiationServiceProvider — registers HTTP ContentNegotiation component dependencies.
 *
 * Registers all content formatters and aliases the ContentFormatter interface
 * to JsonFormatter as the default implementation.
 */
final class ContentNegotiationServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // JsonFormatter — no dependencies
        $container->singleton(JsonFormatter::class, static fn () : JsonFormatter => new JsonFormatter());

        // XmlFormatter — no dependencies
        $container->singleton(XmlFormatter::class, static fn () : XmlFormatter => new XmlFormatter());

        // CsvFormatter — no dependencies
        $container->singleton(CsvFormatter::class, static fn () : CsvFormatter => new CsvFormatter());

        // ContentFormatter interface — alias to JsonFormatter as default
        $container->singleton(ContentFormatter::class, static fn (ContainerInterface $c) : ContentFormatter => $c->get(JsonFormatter::class));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
