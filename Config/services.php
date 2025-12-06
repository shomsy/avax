<?php

declare(strict_types=1);

use Application\Providers\AppServiceProvider;
use Avax\Container\ServiceProviders\Providers\AuthenticationServiceProvider;
use Avax\Container\ServiceProviders\Providers\BootstrapServiceProvider;
use Avax\Container\ServiceProviders\Providers\CommandServiceProvider;
use Avax\Container\ServiceProviders\Providers\ConfigurationServiceProvider;
use Avax\Container\ServiceProviders\Providers\DatabaseServiceProvider;
use Avax\Container\ServiceProviders\Providers\FilesystemServiceProvider;
use Avax\Container\ServiceProviders\Providers\HttpClientServiceProvider;
use Avax\Container\ServiceProviders\Providers\HTTPServiceProvider;
use Avax\Container\ServiceProviders\Providers\LoggingServiceProvider;
use Avax\Container\ServiceProviders\Providers\MiddlewareServiceProvider;
use Avax\Container\ServiceProviders\Providers\RouterServiceProvider;
use Avax\Container\ServiceProviders\Providers\SecurityServiceProvider;
use Avax\Container\ServiceProviders\Providers\SessionServiceProvider;
use Avax\Container\ServiceProviders\Providers\ViewServiceProvider;

return [
    // Core configurations should be registered first
    ConfigurationServiceProvider::class,
    // Filesystem should be registered for services that require file storage
    FilesystemServiceProvider::class,
    // Application lifecycle management
    BootstrapServiceProvider::class,
    // Logging must be available early to capture logs during initialization
    LoggingServiceProvider::class,
    // Register the HTTP Client before other HTTP-related services
    HttpClientServiceProvider::class,
    // Session handling should be ready early for stateful services
    SessionServiceProvider::class,
    // HTTP services like response factories and streams should be ready
    HTTPServiceProvider::class,
    // Database must be available early for any services relying on persistent storage
    DatabaseServiceProvider::class,
    // Migrations
    CommandServiceProvider::class,
    // Middleware pipeline is required before registering router or authentication
    MiddlewareServiceProvider::class,
    // Router depends on middleware for request processing
    RouterServiceProvider::class,
    // Security (e.g., CSRF, guards) requires the router and middleware to be ready
    SecurityServiceProvider::class,
    // Authentication builds upon security and middleware
    AuthenticationServiceProvider::class,
    // Template engine and stuff.
    ViewServiceProvider::class,
    // Business related services
    AppServiceProvider::class,
];
