<?php

declare(strict_types=1);

use Avax\Container\Providers\Auth\AuthenticationServiceProvider;
use Avax\Container\Providers\Auth\SecurityServiceProvider;
use Avax\Container\Providers\Core\ConfigurationServiceProvider;
use Avax\Container\Providers\Core\FilesystemServiceProvider;
use Avax\Container\Providers\Core\LoggingServiceProvider;
use Avax\Container\Providers\Database\DatabaseServiceProvider;
use Avax\Container\Providers\HTTP\HttpClientServiceProvider;
use Avax\Container\Providers\HTTP\HTTPServiceProvider;
use Avax\Container\Providers\HTTP\MiddlewareServiceProvider;
use Avax\Container\Providers\HTTP\RouterServiceProvider;
use Avax\Container\Providers\HTTP\SessionServiceProvider;
use Avax\Container\Providers\HTTP\ViewServiceProvider;

return [
    // Core providers
    ConfigurationServiceProvider::class,
    LoggingServiceProvider::class,
    FilesystemServiceProvider::class,

    // HTTP & Web providers
    HTTPServiceProvider::class,
    HttpClientServiceProvider::class,
    SessionServiceProvider::class,
    MiddlewareServiceProvider::class,
    RouterServiceProvider::class,
    ViewServiceProvider::class,

    // Database
    DatabaseServiceProvider::class,

    // Authentication
    SecurityServiceProvider::class,
    AuthenticationServiceProvider::class,
];
