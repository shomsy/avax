<?php

declare(strict_types=1);

require __DIR__ . '/app_instance.php';

use Avax\Auth\Contracts\AuthInterface;
use Avax\Config\Architecture\DDD\AppPath;
use Avax\Config\Service\Config;
use Avax\Database\Connection\ConnectionManager;
use Avax\DataHandling\ArrayHandling\Arrhae;
use Avax\DataHandling\ObjectHandling\Collections\Collection;
use Avax\DumpDebugger;
use Avax\HTTP\Response\ResponseFactory;
use Avax\HTTP\Router\RouterRuntimeInterface;
use Avax\HTTP\Security\CsrfTokenManager;
use Avax\HTTP\Session\Shared\Contracts\SessionInterface;
use Avax\View\BladeTemplateEngine;
use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/app_instance.php';

// -----------------------------------
// Dependency Injection and Services
// -----------------------------------
if (! function_exists(function: 'app')) {
    function app(string|null $abstract = null) : mixed
    {
        $dependencyInjector = appInstance();

        if ($abstract === null) {
            return $dependencyInjector;
        }

        //        if (! $dependencyInjector->has(id: $abstract)) {
        //            throw new RuntimeException(message: "Action '" . $abstract . "' is not found in the container.");
        //        }

        return $dependencyInjector->get(id: $abstract);
    }
}

// -----------------------------------
// Base Path Utility
// -----------------------------------

if (! function_exists(function: 'base_path')) {
    /**
     * Resolves the base path of the application.
     *
     * @param string $path The relative path to append to the base path.
     *
     * @return string The resolved base path.
     */
    function base_path(string $path = '') : string
    {
        return rtrim(string: AppPath::getRoot(), characters: '/') . '/' . ltrim(string: $path, characters: '/');
    }
}

if (! function_exists(function: 'response')) {
    function response(
        int|null                         $status = null,
        #[SensitiveParameter] array|null $headers = null,
        string                           $body = ''
    ) : ResponseInterface|ResponseFactory
    {
        $headers         ??= [];
        $responseFactory = app(abstract: ResponseFactory::class);

        if ($status === null) {
            return $responseFactory;
        }

        $response = $responseFactory->createResponse(code: $status);

        foreach ($headers as $header => $value) {
            $response = $response->withHeader(name: $header, value: $value);
        }

        $response->getBody()->write(string: $body);

        return $response;
    }
}

// -----------------------------------
// CSRF Token Management
// -----------------------------------

if (! function_exists(function: 'csrf_token')) {
    /**
     * @throws \Exception
     */
    function csrf_token() : string
    {
        $csrfManager = app(abstract: CsrfTokenManager::class);

        if (! $csrfManager instanceof CsrfTokenManager) {
            throw new RuntimeException(message: 'CsrfTokenManager is not registered in the container.');
        }

        return $csrfManager->getToken();
    }
}

// -----------------------------------
// Routing and Views
// -----------------------------------

if (! function_exists(function: 'route')) {
    function route(string $name, array $parameters = []) : string|null
    {
        try {
            // Retrieve the runtime router instance from the dependency injection container.
            $router = app(abstract: RouterRuntimeInterface::class);

            // Fetch the route definition by its name using the retrieved `Router` instance.
            // This name is typically associated with a specific route you defined earlier in the application.
            $route = $router->getRouteByName(name: $name);

            // Extract the path of the route. The `path` property contains the URL pattern for the route.
            $path = $route->path;

            // Inject parameters into the path
            foreach ($parameters as $key => $value) {
                $path = rx_replace(pattern: "\\{{$key}(?:[?*]?)}", replacement: $value, subject: $path);
            }

            // Clean up any optional params not provided
            $path = rx_replace(pattern: '\{[^}]+\}', replacement: '', subject: $path);

            return $path;
        } catch (Throwable $throwable) {
            logger(message: 'Failed to generate route.', context: ['route_name' => $name, 'exception' => $throwable]);

            return null;
        }
    }
}

if (! function_exists(function: 'view')) {
    /**
     * Renders a Blade view and returns an HTTP response.
     *
     * @param string $template The view template to render.
     * @param array  $data     The data to pass to the view.
     */
    function view(string $template, array $data = []) : ResponseInterface
    {
        try {
            $blade = app(abstract: BladeTemplateEngine::class);
            $body  = $blade->render(view: $template, data: $data);

            return response(status: 200, headers: ['Content-Type' => 'text/html'], body: $body);
        } catch (Throwable $throwable) {
            dump('dump view ', $throwable);
            logger(message: 'View rendering failed.', context: ['template' => $template, 'exception' => $throwable]);

            return response(status: 500, body: 'An error occurred while rendering the view.');
        }
    }
}

// -----------------------------------
// Debugging Utilities
// -----------------------------------
// dd() like function
if (! function_exists(function: 'ddx')) {
    #[NoReturn]
    function ddx(mixed ...$args) : never
    {
        DumpDebugger::ddx(...$args);
    }
}

// dump() like function
if (! function_exists(function: 'dumpx')) {
    function dumpx(mixed ...$args) : void
    {
        DumpDebugger::dumpx(...$args);
    }
}

// -----------------------------------
// Session and Logging
// -----------------------------------

if (! function_exists(function: 'session')) {
    function session(string|null $key = null, mixed $value = null) : mixed
    {
        $session = app(abstract: SessionInterface::class);

        if ($key === null) {
            return $session;
        }

        if ($value === null) {
            return $session->get(key: $key);
        }

        $session->set(key: $key, value: $value);

        return null;
    }
}

if (! function_exists(function: 'logger')) {
    function logger(string|null $message = null, array|null $context = null, string $level = 'info')
    {
        $context ??= [];
        $logger  = app(abstract: LoggerInterface::class);

        if ($message === null) {
            return $logger;
        }

        $logger->log(level: $level, message: $message, context: $context);

        return null;
    }
}

// -----------------------------------
// Utility Functions
// -----------------------------------

if (! function_exists(function: 'config')) {
    function config(string $key, mixed $default = null) : mixed
    {
        return app(abstract: Config::class)->get(key: $key, default: $default);
    }
}

if (! function_exists(function: 'storage_path')) {
    function storage_path(string $path = '') : string
    {
        $base = base_path(path: 'storage');

        return rtrim(string: $base, characters: '/') . '/' . ltrim(string: $path, characters: '/');
    }
}

if (! function_exists(function: 'collect')) {
    function collect(iterable $items = []) : Collection
    {
        return new Collection(items: $items);
    }
}

if (! function_exists(function: 'auth')) {
    function auth() : AuthInterface
    {
        return app(abstract: AuthInterface::class);
    }
}

if (! function_exists(function: 'asset')) {
    function asset(string $path) : string
    {
        $baseUrl = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $baseUrl .= $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $baseUrl . '/' . ltrim(string: $path, characters: '/');
    }
}

/**
 * Redirects to a given relative path or route name.
 *
 * @param string $destination A relative path or route name.
 * @param array  $parameters  Parameters for dynamic route segments (if using route names).
 * @param int    $status      HTTP status code for the redirection (default: 302).
 *
 * @return \Psr\Http\Message\ResponseInterface
 */
if (! function_exists(function: 'redirect') && ! function_exists(function: 'redirect')) {
    /**
     * Redirects to a given URL.
     *
     * @param string $url    The URL to redirect to.
     * @param int    $status The HTTP status code for the redirection (default: 302).
     */
    function redirect(string $url, int $status = 302) : ResponseInterface
    {
        $responseFactory = app(abstract: ResponseFactoryInterface::class);

        return $responseFactory
            ->createResponse(code: $status)
            ->withHeader(name: 'Location', value: $url);
    }
}

/**
 * Redirects to a given relative path or route name.
 *
 * @param string $destination A relative path or route name.
 * @param array  $parameters  Parameters for dynamic route segments (if using route names).
 * @param int    $status      HTTP status code for the redirection (default: 302).
 *
 * @return \Psr\Http\Message\ResponseInterface
 */
if (! function_exists(function: 'arrhae') && ! function_exists(function: 'arrhae')) {
    function arrhae(array $array) : Arrhae
    {
        return new Arrhae(items: $array);
    }
}

if (! function_exists(function: 'connection')) {
    /**
     * Retrieves a PDO database connection.
     *
     * @param string|null $connectionName The name of the database connection to retrieve. Defaults to null for the default connection.
     *
     * @return \PDO The PDO database connection instance.
     *
     * @throws RuntimeException If the database connection service is not available in the dependency injection container.
     * @throws \Throwable
     */
    function connection(string|null $connectionName = null) : PDO
    {
        /** @var ConnectionManager $databaseManager */
        $databaseManager = app(abstract: ConnectionManager::class);

        if (! $databaseManager instanceof ConnectionManager) {
            throw new RuntimeException(message: 'Database connection service is not registered in DI container.');
        }

        return $databaseManager->getPdo(name: $connectionName);
    }
}

if (! function_exists(function: 'preview_text')) {
    /**
     * Shortens the given text for preview purposes.
     *
     * @param int $limit Number of characters to show
     *
     * @return string Truncated text with ellipsis if necessary.
     */
    function preview_text(string $text, int $limit = 80) : string
    {
        $text = strip_tags(string: $text);

        return mb_strlen(string: $text) > $limit
            ? mb_substr(string: $text, start: 0, length: $limit - 3) . '...'
            : $text;
    }
}