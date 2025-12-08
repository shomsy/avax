<?php

declare(strict_types=1);

use Avax\Auth\Contracts\AuthenticationServiceInterface;
use Avax\Config\Architecture\DDD\AppPath;
use Avax\Container\Containers\DependencyInjector;
use Avax\Container\Contracts\ContainerInterface;
use Avax\Database\DatabaseConnection;
use Avax\DataHandling\ArrayHandling\Arrhae;
use Avax\DataHandling\ObjectHandling\Collections\Collection;
use Avax\DumpDebugger;
use Avax\HTTP\Response\ResponseFactory;
use Avax\HTTP\Router\Router;
use Avax\HTTP\Security\CsrfTokenManager;
use Avax\HTTP\Session\Contracts\SessionInterface;
use Avax\View\BladeTemplateEngine;
use Infrastructure\Config\Service\Config;
use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

// -----------------------------------
// Dependency Injection and Services
// -----------------------------------

if (! function_exists(function: 'appInstance')) {
    function appInstance(ContainerInterface|null $instance = null) : DependencyInjector
    {
        static $container = null;

        if ($instance instanceof ContainerInterface) {
            if (! $instance instanceof DependencyInjector) {
                throw new RuntimeException(message: "Only DependencyInjector instances can be used for appInstance.");
            }

            $container = $instance;
        }

        if ($container === null) {
            throw new RuntimeException(
                message: "Container instance is not initialized. Please set the container first."
            );
        }

        return $container;
    }
}

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
        int|null $status = null,
        array    $headers = [],
        string   $body = ''
    ) : ResponseInterface|ResponseFactory {
        $responseFactory = app(abstract: ResponseFactory::class);

        if ($status === null) {
            return $responseFactory;
        }

        $response = $responseFactory->createResponse($status);

        foreach ($headers as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        $response->getBody()->write($body);

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
            throw new RuntimeException(message: "CsrfTokenManager is not registered in the container.");
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
            // Retrieve the `Router` instance from the dependency injection container.
            // The `app` function resolves a service by its class (or abstract type).
            $router = app(abstract: Router::class);

            // Fetch the route definition by its name using the retrieved `Router` instance.
            // This name is typically associated with a specific route you defined earlier in the application.
            $route = $router->getRouteByName($name);

            // Extract the path of the route. The `path` property contains the URL pattern for the route.
            $path = $route->path;

            // Inject parameters into the path
            foreach ($parameters as $key => $value) {
                $path = preg_replace("/\{{$key}(?:[?*]?)}/", $value, $path);
            }

            // Clean up any optional params not provided
            $path = preg_replace('/\{[^}]+\}/', '', $path);

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
            $blade = app(BladeTemplateEngine::class);
            $body  = $blade->render($template, $data);

            return response(status: 200, headers: ['Content-Type' => 'text/html'], body: $body);
        } catch (Throwable $throwable) {
            dump('dump view ', $throwable);
            logger('View rendering failed.', ['template' => $template, 'exception' => $throwable]);

            return response(status: 500, body: 'An error occurred while rendering the view.');
        }
    }
}

// -----------------------------------
// Debugging Utilities
// -----------------------------------
// dd() like function
if (! function_exists('ddx')) {
    #[NoReturn]
    function ddx(mixed ...$args) : never
    {
        DumpDebugger::ddx(...$args);
    }
}

// dump() like function
if (! function_exists('dumpx')) {
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
            return $session->get($key);
        }

        $session->set($key, $value);

        return null;
    }
}

if (! function_exists(function: 'logger')) {
    function logger(string|null $message = null, array $context = [], string $level = 'info')
    {
        $logger = app(abstract: LoggerInterface::class);

        if ($message === null) {
            return $logger;
        }

        $logger->log($level, $message, $context);

        return null;
    }
}

// -----------------------------------
// Utility Functions
// -----------------------------------

if (! function_exists(function: 'config')) {
    function config(string $key, mixed $default = null) : mixed
    {
        return app(abstract: Config::class)->get($key, $default);
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
    function auth() : AuthenticationServiceInterface
    {
        return app(abstract: AuthenticationServiceInterface::class);
    }
}

if (! function_exists('asset')) {
    function asset(string $path) : string
    {
        $baseUrl = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $baseUrl .= $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $baseUrl . '/' . ltrim($path, '/');
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
if (! function_exists(function: 'redirect') && ! function_exists('redirect')) {
    /**
     * Redirects to a given URL.
     *
     * @param string $url    The URL to redirect to.
     * @param int    $status The HTTP status code for the redirection (default: 302).
     */
    function redirect(string $url, int $status = 302) : ResponseInterface
    {
        $responseFactory = app(ResponseFactoryInterface::class);

        return $responseFactory
            ->createResponse($status)
            ->withHeader('Location', $url);
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
if (! function_exists(function: 'arrhae') && ! function_exists('arrhae')) {
    function arrhae(array $array) : Arrhae
    {
        return new Arrhae($array);
    }
}

if (! function_exists('connection')) {
    /**
     * Retrieves a PDO database connection.
     *
     * @param string|null $connectionName The name of the database connection to retrieve. Defaults to null for the default connection.
     *
     * @return PDO The PDO database connection instance.
     * @throws RuntimeException If the database connection service is not available in the dependency injection container.
     */
    function connection(string $connectionName = null) : PDO
    {
        /** @var DatabaseConnection $databaseManager */
        $databaseManager = app(abstract: DatabaseConnection::class);

        if (! $databaseManager instanceof DatabaseConnection) {
            throw new RuntimeException(message: 'Database connection service is not registered in DI container.');
        }

        return $databaseManager->getConnection(connectionName: $connectionName);
    }

    if (! function_exists('preview_text')) {
        /**
         * Shortens the given text for preview purposes.
         *
         * @param string $text
         * @param int    $limit Number of characters to show
         *
         * @return string Truncated text with ellipsis if necessary.
         */
        function preview_text(string $text, int $limit = 80) : string
        {
            $text = strip_tags($text);

            return mb_strlen($text) > $limit
                ? mb_substr($text, 0, $limit - 3) . '...'
                : $text;
        }
    }
}


