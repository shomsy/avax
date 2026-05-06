<?php

declare(strict_types=1);

/**
 * Response shortcuts for global access.
 */

use Avax\Components\HTTP\Response\ResponseFactory;
use Psr\Http\Message\ResponseInterface;

if (! function_exists('response')) {
    /**
     * Create a new response instance.
     */
    function response(string $content = '', int $status = 200, array $headers = []): ResponseInterface
    {
        return new ResponseFactory()->create($status, $headers, $content);
    }
}

if (! function_exists('json_response')) {
    /**
     * Create a JSON response.
     */
    function json_response(mixed $data, int $status = 200, array $headers = []): ResponseInterface
    {
        return new ResponseFactory()->json($data, $status, $headers);
    }
}

if (! function_exists('abort')) {
    /**
     * Throw an HTTP exception or return an error response.
     */
    function abort(int $code, string $message = ''): never
    {
        throw new RuntimeException(sprintf('HTTP %d: ', $code).($message ?: 'Error'), $code);
    }
}
