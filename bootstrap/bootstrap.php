<?php

declare(strict_types=1);

// Load composer autoloader FIRST
require_once dirname(__DIR__) . '/vendor/autoload.php';

require_once dirname(__DIR__) . '/Foundation/Helpers/helpers.php';

use Avax\Container\Features\Operate\Boot\Application;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

// The builder will automatically load core providers.
// Any custom, application-specific providers can be added via ->withProviders()
try {
    return Application::start(root: dirname(__DIR__))
        ->exposeWeb(path: dirname(__DIR__) . '/Presentation/HTTP/routes/web.routes.php')
        ->build();
} catch (\Avax\Container\Features\Core\Exceptions\ContainerExceptionInterface $e) {
    // Handle container-specific exceptions
    throw $e;
} catch (NotFoundExceptionInterface $e) {
    // Handle service not found
    throw $e;
} catch (ContainerExceptionInterface | Throwable $e) {
    // Handle generic container or system errors
    throw $e;
}
