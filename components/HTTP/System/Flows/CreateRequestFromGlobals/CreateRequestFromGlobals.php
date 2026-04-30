<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Flows\CreateRequestFromGlobals;

use Avax\Components\HTTP\System\Capabilities\Request;
use Avax\Components\HTTP\System\Capabilities\Uri;

final class CreateRequestFromGlobals
{
    public static function execute() : Request
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = self::getUri();
        $headers = self::getHeaders();
        $body   = self::getBody();

        return new Request(
            $method,
            $uri,
            'HTTP/1.1',
            $headers,
            $body,
        );
    }

    private static function getUri() : Uri
    {
        $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        $port   = $_SERVER['SERVER_PORT'] ?? 80;
        $path   = $_SERVER['REQUEST_URI'] ?? '/';
        $query  = $_SERVER['QUERY_STRING'] ?? '';

        return new Uri($scheme, $host, $port, $path, $query);
    }

    private static function getHeaders() : array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = substr($key, 5);
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    private static function getBody() : string
    {
        return file_get_contents('php://input') ?: '';
    }
}
