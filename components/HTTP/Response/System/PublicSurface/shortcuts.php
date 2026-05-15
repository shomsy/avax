<?php

declare(strict_types=1);

/**
 * Response shortcuts for global access.
 *
 * These shortcuts resolve the Responses service from the container.
 * In production, the container is bootstrapped and Responses is registered by ResponseServiceProvider.
 * In testing, the container may be mocked or Responses may be set directly.
 */

use Avax\Components\HTTP\Response\System\PublicSurface\Responses;
use Psr\Http\Message\ResponseInterface;

if (! function_exists('response')) {
    /**
     * Create a new response via intelligent type dispatch.
     */
    function response(mixed $data = '', int $status = 200): ResponseInterface
    {
        return responses()->send(data: $data, status: $status);
    }
}

if (! function_exists('responses')) {
    /**
     * Resolve the Responses facade from the container.
     */
    function responses(): Responses
    {
        return app(Responses::class);
    }
}

if (! function_exists('abort')) {
    /**
     * Throw an HTTP exception.
     */
    function abort(int $code, string $message = ''): never
    {
        throw new RuntimeException(sprintf('HTTP %d: ', $code).($message ?: 'Error'), $code);
    }
}
