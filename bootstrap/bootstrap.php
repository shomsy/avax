<?php

declare(strict_types=1);

// Load composer autoloader FIRST
require_once dirname(__DIR__).'/vendor/autoload.php';

use Avax\Container\Core\AppFactory;
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

$providers = [
    ConfigurationServiceProvider::class,
    FilesystemServiceProvider::class,
    LoggingServiceProvider::class,
    AuthenticationServiceProvider::class,
    SecurityServiceProvider::class,
    DatabaseServiceProvider::class,
    HTTPServiceProvider::class,
    MiddlewareServiceProvider::class,
    RouterServiceProvider::class,
    SessionServiceProvider::class,
    ViewServiceProvider::class,
    HttpClientServiceProvider::class,
];

$routes = dirname(__DIR__).'/Presentation/HTTP/routes/web.routes.php';
$cacheDir = dirname(__DIR__).'/storage/cache';

return AppFactory::http(
    providers: $providers,
    routes   : $routes,
    cacheDir : $cacheDir,
    debug    : true
);
