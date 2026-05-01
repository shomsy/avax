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
     *
     * @param string $content
     * @param int   $status
     * @param array $headers
     *
     * @return ResponseInterface
     */
    function response(string $content = '', int $status = 200, array $headers = []) : ResponseInterface
    {
        return (new ResponseFactory())->create($status, $headers, $content);
    }
}

if (! function_exists('json_response')) {
    /**
     * Create a JSON response.
     *
     * @param mixed $data
     * @param int $status
     * @param array $headers
     *
     * @return ResponseInterface
     */
    function json_response(mixed $data, int $status = 200, array $headers = []) : ResponseInterface
    {
        return (new ResponseFactory())->json($data, $status, $headers);
    }
}

if (! function_exists('abort')) {
    /**
     * Throw an HTTP exception or return an error response.
     *
     * @param int $code
     * @param string $message
     *
     * @return never
     */
    function abort(int $code, string $message = '') : never
    {
        throw new RuntimeException("HTTP {$code}: " . ($message ?: 'Error'), $code);
    }
}
