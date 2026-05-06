<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\Enums;

enum HttpMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case HEAD = 'HEAD';
    case PATCH = 'PATCH';
    case OPTIONS = 'OPTIONS';
    case TRACE = 'TRACE';
    case CONNECT = 'CONNECT';

    public static function isSupported(string $method): bool
    {
        return self::tryFrom(strtoupper($method)) !== null;
    }
}
