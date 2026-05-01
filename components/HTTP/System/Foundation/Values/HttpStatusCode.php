<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Foundation\Values;

use ValueError;

enum HttpStatusCode: int
{
    // 2xx Success
    case OK         = 200;
    case CREATED    = 201;
    case ACCEPTED   = 202;
    case NO_CONTENT = 204;

    // 3xx Redirection
    case MOVED_PERMANENTLY  = 301;
    case FOUND              = 302;
    case SEE_OTHER          = 303;
    case NOT_MODIFIED       = 304;
    case TEMPORARY_REDIRECT = 307;
    case PERMANENT_REDIRECT = 308;

    // 4xx Client Error
    case BAD_REQUEST            = 400;
    case UNAUTHORIZED           = 401;
    case FORBIDDEN              = 403;
    case NOT_FOUND              = 404;
    case METHOD_NOT_ALLOWED     = 405;
    case NOT_ACCEPTABLE         = 406;
    case CONFLICT               = 409;
    case GONE                   = 410;
    case LENGTH_REQUIRED        = 411;
    case PRECONDITION_FAILED    = 412;
    case PAYLOAD_TOO_LARGE      = 413;
    case URI_TOO_LONG           = 414;
    case UNSUPPORTED_MEDIA_TYPE = 415;
    case RANGE_NOT_SATISFIABLE  = 416;
    case EXPECTATION_FAILED     = 417;
    case UNPROCESSABLE_ENTITY   = 422;
    case LOCKED                 = 423;
    case FAILED_DEPENDENCY      = 424;
    case TOO_EARLY              = 425;
    case UPGRADE_REQUIRED       = 426;
    case PRECONDITION_REQUIRED  = 428;
    case TOO_MANY_REQUESTS      = 429;

    // 5xx Server Error
    case INTERNAL_SERVER_ERROR           = 500;
    case NOT_IMPLEMENTED                 = 501;
    case BAD_GATEWAY                     = 502;
    case SERVICE_UNAVAILABLE             = 503;
    case GATEWAY_TIMEOUT                 = 504;
    case HTTP_VERSION_NOT_SUPPORTED      = 505;
    case INSUFFICIENT_STORAGE            = 507;
    case LOOP_DETECTED                   = 508;
    case NOT_EXTENDED                    = 510;
    case NETWORK_AUTHENTICATION_REQUIRED = 511;

    /**
     * Create an HttpStatusCode from an integer status code.
     *
     * @throws ValueError if the code is not a recognized status code
     */
    public static function fromCode(int $code): self
    {
        return self::from($code);
    }

    /**
     * Try to create an HttpStatusCode from an integer status code.
     * Returns null if the code is not recognized.
     */
    public static function tryFromCode(int $code): ?self
    {
        return self::tryFrom($code);
    }

    /**
     * Check if the status code is in the 1xx (Informational) range.
     */
    public function isInformational(): bool
    {
        return $this->value >= 100 && $this->value < 200;
    }

    /**
     * Check if the status code is in the 2xx (Success) range.
     */
    public function isSuccess(): bool
    {
        return $this->value >= 200 && $this->value < 300;
    }

    /**
     * Check if the status code is exactly 200 OK.
     */
    public function isOk(): bool
    {
        return $this === self::OK;
    }

    /**
     * Check if the status code is in the 3xx (Redirection) range.
     */
    public function isRedirect(): bool
    {
        return $this->value >= 300 && $this->value < 400;
    }

    /**
     * Check if the status code indicates an error (4xx or 5xx).
     */
    public function isError(): bool
    {
        if ($this->isClientError()) {
            return true;
        }

        return $this->isServerError();
    }

    /**
     * Check if the status code is in the 4xx (Client Error) range.
     */
    public function isClientError(): bool
    {
        return $this->value >= 400 && $this->value < 500;
    }

    /**
     * Check if the status code is in the 5xx (Server Error) range.
     */
    public function isServerError(): bool
    {
        return $this->value >= 500 && $this->value < 600;
    }

    /**
     * Get the standard HTTP reason phrase for this status code.
     */
    public function getReasonPhrase(): string
    {
        return match ($this) {
            // 2xx
            self::OK         => 'OK',
            self::CREATED    => 'Created',
            self::ACCEPTED   => 'Accepted',
            self::NO_CONTENT => 'No Content',

            // 3xx
            self::MOVED_PERMANENTLY  => 'Moved Permanently',
            self::FOUND              => 'Found',
            self::SEE_OTHER          => 'See Other',
            self::NOT_MODIFIED       => 'Not Modified',
            self::TEMPORARY_REDIRECT => 'Temporary Redirect',
            self::PERMANENT_REDIRECT => 'Permanent Redirect',

            // 4xx
            self::BAD_REQUEST            => 'Bad Request',
            self::UNAUTHORIZED           => 'Unauthorized',
            self::FORBIDDEN              => 'Forbidden',
            self::NOT_FOUND              => 'Not Found',
            self::METHOD_NOT_ALLOWED     => 'Method Not Allowed',
            self::NOT_ACCEPTABLE         => 'Not Acceptable',
            self::CONFLICT               => 'Conflict',
            self::GONE                   => 'Gone',
            self::LENGTH_REQUIRED        => 'Length Required',
            self::PRECONDITION_FAILED    => 'Precondition Failed',
            self::PAYLOAD_TOO_LARGE      => 'Payload Too Large',
            self::URI_TOO_LONG           => 'URI Too Long',
            self::UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
            self::RANGE_NOT_SATISFIABLE  => 'Range Not Satisfiable',
            self::EXPECTATION_FAILED     => 'Expectation Failed',
            self::UNPROCESSABLE_ENTITY   => 'Unprocessable Entity',
            self::LOCKED                 => 'Locked',
            self::FAILED_DEPENDENCY      => 'Failed Dependency',
            self::TOO_EARLY              => 'Too Early',
            self::UPGRADE_REQUIRED       => 'Upgrade Required',
            self::PRECONDITION_REQUIRED  => 'Precondition Required',
            self::TOO_MANY_REQUESTS      => 'Too Many Requests',

            // 5xx
            self::INTERNAL_SERVER_ERROR           => 'Internal Server Error',
            self::NOT_IMPLEMENTED                 => 'Not Implemented',
            self::BAD_GATEWAY                     => 'Bad Gateway',
            self::SERVICE_UNAVAILABLE             => 'Service Unavailable',
            self::GATEWAY_TIMEOUT                 => 'Gateway Timeout',
            self::HTTP_VERSION_NOT_SUPPORTED      => 'HTTP Version Not Supported',
            self::INSUFFICIENT_STORAGE            => 'Insufficient Storage',
            self::LOOP_DETECTED                   => 'Loop Detected',
            self::NOT_EXTENDED                    => 'Not Extended',
            self::NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
        };
    }
}
