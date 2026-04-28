<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Capabilities\Message;

final class ResolveReasonPhrase
{
    private const array DEFAULTS
        = [
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            204 => 'No Content',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
        ];

    public function __invoke(int $statusCode, string $reasonPhrase = '') : string
    {
        return $reasonPhrase !== '' ? $reasonPhrase : (self::DEFAULTS[$statusCode] ?? '');
    }
}
