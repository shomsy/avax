<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\Enums;

enum HttpReasonPhrase: string
{
    case OK                    = 'OK';
    case CREATED               = 'Created';
    case ACCEPTED              = 'Accepted';
    case NO_CONTENT            = 'No Content';
    case MOVED_PERMANENTLY     = 'Moved Permanently';
    case FOUND                 = 'Found';
    case SEE_OTHER             = 'See Other';
    case NOT_MODIFIED          = 'Not Modified';
    case TEMPORARY_REDIRECT    = 'Temporary Redirect';
    case PERMANENT_REDIRECT    = 'Permanent Redirect';
    case BAD_REQUEST           = 'Bad Request';
    case UNAUTHORIZED          = 'Unauthorized';
    case FORBIDDEN             = 'Forbidden';
    case NOT_FOUND             = 'Not Found';
    case METHOD_NOT_ALLOWED    = 'Method Not Allowed';
    case CONFLICT              = 'Conflict';
    case GONE                  = 'Gone';
    case UNPROCESSABLE_ENTITY  = 'Unprocessable Entity';
    case TOO_MANY_REQUESTS     = 'Too Many Requests';
    case INTERNAL_SERVER_ERROR = 'Internal Server Error';
    case NOT_IMPLEMENTED       = 'Not Implemented';
    case BAD_GATEWAY           = 'Bad Gateway';
    case SERVICE_UNAVAILABLE   = 'Service Unavailable';
    case GATEWAY_TIMEOUT       = 'Gateway Timeout';

    public static function fromStatusCode(int $code): ?self
    {
        return match ($code) {
            200     => self::OK,
            201     => self::CREATED,
            202     => self::ACCEPTED,
            204     => self::NO_CONTENT,
            301     => self::MOVED_PERMANENTLY,
            302     => self::FOUND,
            303     => self::SEE_OTHER,
            304     => self::NOT_MODIFIED,
            307     => self::TEMPORARY_REDIRECT,
            308     => self::PERMANENT_REDIRECT,
            400     => self::BAD_REQUEST,
            401     => self::UNAUTHORIZED,
            403     => self::FORBIDDEN,
            404     => self::NOT_FOUND,
            405     => self::METHOD_NOT_ALLOWED,
            409     => self::CONFLICT,
            410     => self::GONE,
            422     => self::UNPROCESSABLE_ENTITY,
            429     => self::TOO_MANY_REQUESTS,
            500     => self::INTERNAL_SERVER_ERROR,
            501     => self::NOT_IMPLEMENTED,
            502     => self::BAD_GATEWAY,
            503     => self::SERVICE_UNAVAILABLE,
            504     => self::GATEWAY_TIMEOUT,
            default => null,
        };
    }
}
