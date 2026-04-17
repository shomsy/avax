<?php

declare(strict_types=1);

namespace Avax\HTTP\Request;

use Avax\Container\Providers\ServiceProvider;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\Configuration\CreateRequestFromIncomingHttp;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ProtocolVersion\NormalizeProtocolVersion;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseBodyByContentType;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseFormBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseJsonBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\NormalizeUploadedFiles;
use Override;

/**
 * Service provider for HTTP Request component.
 *
 * Registers all Request-related services with the DI container,
 * enabling dependency injection for request handling components.
 */
final class RequestServiceProvider extends ServiceProvider
{


    public function dependsOn() : array
    {
        return [];
    }

    #[Override]
    public function register() : void
    {
        $this->registerBodyParsers();
        $this->registerNetwork();
        $this->registerUtilities();
    }

    private function registerBodyParsers() : void
    {
        if (! $this->app->has(id: ParseBodyByContentType::class)) {
            $this->app->singleton(id: ParseBodyByContentType::class, implementation: ParseBodyByContentType::class);
        }

        if (! $this->app->has(id: ParseJsonBody::class)) {
            $this->app->singleton(id: ParseJsonBody::class, implementation: ParseJsonBody::class);
        }

        if (! $this->app->has(id: ParseFormBody::class)) {
            $this->app->singleton(id: ParseFormBody::class, implementation: ParseFormBody::class);
        }
    }

    private function registerNetwork() : void
    {
        // Network services will be registered here when available
    }

    private function registerUtilities() : void
    {
        if (! $this->app->has(id: NormalizeProtocolVersion::class)) {
            $this->app->singleton(id: NormalizeProtocolVersion::class, implementation: NormalizeProtocolVersion::class);
        }

        if (! $this->app->has(id: NormalizeUploadedFiles::class)) {
            $this->app->singleton(id: NormalizeUploadedFiles::class, implementation: NormalizeUploadedFiles::class);
        }

        if (! $this->app->has(id: CreateRequestFromIncomingHttp::class)) {
            $this->app->singleton(id: CreateRequestFromIncomingHttp::class, implementation: CreateRequestFromIncomingHttp::class);
        }
    }

    public function boot() : void
    {
        // No boot-time side effects required
    }
}