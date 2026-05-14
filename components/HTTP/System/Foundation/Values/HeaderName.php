<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Foundation\Values;

use ValueError;
use function sprintf;
use function strcasecmp;
use function strtolower;
use function trim;
use function ucwords;

enum HeaderName: string
{
    // Standard Request Headers
    case ACCEPT              = 'Accept';
    case ACCEPT_CHARSET      = 'Accept-Charset';
    case ACCEPT_ENCODING     = 'Accept-Encoding';
    case ACCEPT_LANGUAGE     = 'Accept-Language';
    case AUTHORIZATION       = 'Authorization';
    case CACHE_CONTROL       = 'Cache-Control';
    case CONNECTION          = 'Connection';
    case CONTENT_LENGTH      = 'Content-Length';
    case CONTENT_TYPE        = 'Content-Type';
    case COOKIE              = 'Cookie';
    case DATE                = 'Date';
    case EXPECT              = 'Expect';
    case FORWARDED           = 'Forwarded';
    case FROM                = 'From';
    case HOST                = 'Host';
    case IF_MATCH            = 'If-Match';
    case IF_MODIFIED_SINCE   = 'If-Modified-Since';
    case IF_NONE_MATCH       = 'If-None-Match';
    case IF_RANGE            = 'If-Range';
    case IF_UNMODIFIED_SINCE = 'If-Unmodified-Since';
    case MAX_FORWARDS        = 'Max-Forwards';
    case ORIGIN              = 'Origin';
    case PRAGMA              = 'Pragma';
    case PROXY_AUTHORIZATION = 'Proxy-Authorization';
    case RANGE               = 'Range';
    case REFERER             = 'Referer';
    case TE                  = 'TE';
    case TRAILER             = 'Trailer';
    case TRANSFER_ENCODING   = 'Transfer-Encoding';
    case USER_AGENT          = 'User-Agent';
    case UPGRADE             = 'Upgrade';
    case VIA                 = 'Via';
    case WARNING             = 'Warning';

    // Standard Response Headers
    case ACCEPT_RANGES             = 'Accept-Ranges';
    case AGE                       = 'Age';
    case ALLOW                     = 'Allow';
    case ALT_SVC                   = 'Alt-Svc';
    case CDN_CACHE_CONTROL         = 'CDN-Cache-Control';
    case CONTENT_DISPOSITION       = 'Content-Disposition';
    case CONTENT_ENCODING          = 'Content-Encoding';
    case CONTENT_LANGUAGE          = 'Content-Language';
    case CONTENT_LOCATION          = 'Content-Location';
    case CONTENT_RANGE             = 'Content-Range';
    case ETAG                      = 'ETag';
    case EXPIRES                   = 'Expires';
    case LAST_MODIFIED             = 'Last-Modified';
    case LINK                      = 'Link';
    case LOCATION                  = 'Location';
    case RETRY_AFTER               = 'Retry-After';
    case SERVER                    = 'Server';
    case SET_COOKIE                = 'Set-Cookie';
    case STRICT_TRANSPORT_SECURITY = 'Strict-Transport-Security';
    case TRAILERS                  = 'Trailers';
    case VARY                      = 'Vary';
    case WWW_AUTHENTICATE          = 'WWW-Authenticate';

    // Common Custom / Application Headers
    case X_REQUESTED_WITH                 = 'X-Requested-With';
    case X_FORWARDED_FOR                  = 'X-Forwarded-For';
    case X_FORWARDED_PROTO                = 'X-Forwarded-Proto';
    case X_FORWARDED_HOST                 = 'X-Forwarded-Host';
    case X_FORWARDED_PORT                 = 'X-Forwarded-Port';
    case X_REAL_IP                        = 'X-Real-IP';
    case X_HTTP_METHOD_OVERRIDE           = 'X-HTTP-Method-Override';
    case X_CSRF_TOKEN                     = 'X-CSRF-Token';
    case X_XSS_PROTECTION                 = 'X-XSS-Protection';
    case X_FRAME_OPTIONS                  = 'X-Frame-Options';
    case X_CONTENT_TYPE_OPTIONS           = 'X-Content-Type-Options';
    case X_POWERED_BY                     = 'X-Powered-By';
    case X_RATELIMIT_LIMIT                = 'X-RateLimit-Limit';
    case X_RATELIMIT_REMAINING            = 'X-RateLimit-Remaining';
    case X_RATELIMIT_RESET                = 'X-RateLimit-Reset';
    case ACCESS_CONTROL_ALLOW_ORIGIN      = 'Access-Control-Allow-Origin';
    case ACCESS_CONTROL_ALLOW_METHODS     = 'Access-Control-Allow-Methods';
    case ACCESS_CONTROL_ALLOW_HEADERS     = 'Access-Control-Allow-Headers';
    case ACCESS_CONTROL_ALLOW_CREDENTIALS = 'Access-Control-Allow-Credentials';
    case ACCESS_CONTROL_EXPOSE_HEADERS    = 'Access-Control-Expose-Headers';
    case ACCESS_CONTROL_MAX_AGE           = 'Access-Control-Max-Age';
    case ACCESS_CONTROL_REQUEST_METHOD    = 'Access-Control-Request-Method';
    case ACCESS_CONTROL_REQUEST_HEADERS   = 'Access-Control-Request-Headers';
    case CONTENT_SECURITY_POLICY          = 'Content-Security-Policy';
    case REFERRER_POLICY                  = 'Referrer-Policy';
    case PERMISSIONS_POLICY               = 'Permissions-Policy';
    case CROSS_ORIGIN_OPENER_POLICY       = 'Cross-Origin-Opener-Policy';
    case CROSS_ORIGIN_RESOURCE_POLICY     = 'Cross-Origin-Resource-Policy';

    /**
     * Create a HeaderName from a header string (case-insensitive).
     *
     * @throws ValueError if the header name is not recognized
     */
    public static function fromName(string $name) : self
    {
        $normalized = ucwords(strtolower(trim($name)), '-');

        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $normalized) === 0) {
                return $case;
            }
        }

        throw new ValueError(sprintf('Unknown HTTP header name: "%s"', $name));
    }

    /**
     * Check if a string is a valid, recognized HTTP header name.
     */
    public static function isValid(string $name) : bool
    {
        return self::tryFromName($name) instanceof HeaderName;
    }

    /**
     * Try to create a HeaderName from a header string (case-insensitive).
     * Returns null if the header name is not recognized.
     */
    public static function tryFromName(string $name) : self|null
    {
        $normalized = ucwords(strtolower(trim($name)), '-');

        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $normalized) === 0) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Get the canonical header name string.
     * This is equivalent to accessing the backed value directly.
     */
    public function toString() : string
    {
        return $this->value;
    }

    /**
     * Check if this header is used in both requests and responses.
     */
    public function isBidirectional() : bool
    {
        return ! $this->isRequestHeader() && ! $this->isResponseHeader();
    }

    /**
     * Check if this is a request header (commonly sent by clients).
     */
    public function isRequestHeader() : bool
    {
        return match ($this) {
            self::ACCEPT,
            self::ACCEPT_CHARSET,
            self::ACCEPT_ENCODING,
            self::ACCEPT_LANGUAGE,
            self::AUTHORIZATION,
            self::CACHE_CONTROL,
            self::CONNECTION,
            self::CONTENT_LENGTH,
            self::CONTENT_TYPE,
            self::COOKIE,
            self::DATE,
            self::EXPECT,
            self::FORWARDED,
            self::FROM,
            self::HOST,
            self::IF_MATCH,
            self::IF_MODIFIED_SINCE,
            self::IF_NONE_MATCH,
            self::IF_RANGE,
            self::IF_UNMODIFIED_SINCE,
            self::MAX_FORWARDS,
            self::ORIGIN,
            self::PRAGMA,
            self::PROXY_AUTHORIZATION,
            self::RANGE,
            self::REFERER,
            self::TE,
            self::TRAILER,
            self::TRANSFER_ENCODING,
            self::USER_AGENT,
            self::UPGRADE,
            self::VIA,
            self::WARNING,
            self::X_REQUESTED_WITH,
            self::X_FORWARDED_FOR,
            self::X_FORWARDED_PROTO,
            self::X_FORWARDED_HOST,
            self::X_FORWARDED_PORT,
            self::X_REAL_IP,
            self::X_HTTP_METHOD_OVERRIDE,
            self::X_CSRF_TOKEN,
            self::ACCESS_CONTROL_REQUEST_METHOD,
            self::ACCESS_CONTROL_REQUEST_HEADERS => true,
            default                              => false,
        };
    }

    /**
     * Check if this is a response header (commonly sent by servers).
     */
    public function isResponseHeader() : bool
    {
        return match ($this) {
            self::ACCEPT_RANGES,
            self::AGE,
            self::ALLOW,
            self::ALT_SVC,
            self::CDN_CACHE_CONTROL,
            self::CONTENT_DISPOSITION,
            self::CONTENT_ENCODING,
            self::CONTENT_LANGUAGE,
            self::CONTENT_LOCATION,
            self::CONTENT_RANGE,
            self::ETAG,
            self::EXPIRES,
            self::LAST_MODIFIED,
            self::LINK,
            self::LOCATION,
            self::RETRY_AFTER,
            self::SERVER,
            self::SET_COOKIE,
            self::STRICT_TRANSPORT_SECURITY,
            self::TRAILERS,
            self::VARY,
            self::WWW_AUTHENTICATE,
            self::X_POWERED_BY,
            self::X_RATELIMIT_LIMIT,
            self::X_RATELIMIT_REMAINING,
            self::X_RATELIMIT_RESET,
            self::X_XSS_PROTECTION,
            self::X_FRAME_OPTIONS,
            self::X_CONTENT_TYPE_OPTIONS,
            self::ACCESS_CONTROL_ALLOW_ORIGIN,
            self::ACCESS_CONTROL_ALLOW_METHODS,
            self::ACCESS_CONTROL_ALLOW_HEADERS,
            self::ACCESS_CONTROL_ALLOW_CREDENTIALS,
            self::ACCESS_CONTROL_EXPOSE_HEADERS,
            self::ACCESS_CONTROL_MAX_AGE,
            self::CONTENT_SECURITY_POLICY,
            self::REFERRER_POLICY,
            self::PERMISSIONS_POLICY,
            self::CROSS_ORIGIN_OPENER_POLICY,
            self::CROSS_ORIGIN_RESOURCE_POLICY => true,
            default                            => false,
        };
    }

    /**
     * Check if this is a security-related header.
     */
    public function isSecurityHeader() : bool
    {
        return match ($this) {
            self::AUTHORIZATION,
            self::PROXY_AUTHORIZATION,
            self::WWW_AUTHENTICATE,
            self::STRICT_TRANSPORT_SECURITY,
            self::CONTENT_SECURITY_POLICY,
            self::X_CSRF_TOKEN,
            self::X_XSS_PROTECTION,
            self::X_FRAME_OPTIONS,
            self::X_CONTENT_TYPE_OPTIONS,
            self::REFERRER_POLICY,
            self::PERMISSIONS_POLICY,
            self::CROSS_ORIGIN_OPENER_POLICY,
            self::CROSS_ORIGIN_RESOURCE_POLICY,
            self::ACCESS_CONTROL_ALLOW_ORIGIN,
            self::ACCESS_CONTROL_ALLOW_METHODS,
            self::ACCESS_CONTROL_ALLOW_HEADERS,
            self::ACCESS_CONTROL_ALLOW_CREDENTIALS => true,
            default                                => false,
        };
    }

    /**
     * Check if this is a CORS-related header.
     */
    public function isCorsHeader() : bool
    {
        return match ($this) {
            self::ORIGIN,
            self::ACCESS_CONTROL_ALLOW_ORIGIN,
            self::ACCESS_CONTROL_ALLOW_METHODS,
            self::ACCESS_CONTROL_ALLOW_HEADERS,
            self::ACCESS_CONTROL_ALLOW_CREDENTIALS,
            self::ACCESS_CONTROL_EXPOSE_HEADERS,
            self::ACCESS_CONTROL_MAX_AGE,
            self::ACCESS_CONTROL_REQUEST_METHOD,
            self::ACCESS_CONTROL_REQUEST_HEADERS => true,
            default                              => false,
        };
    }

    /**
     * Check if this is a caching-related header.
     */
    public function isCachingHeader() : bool
    {
        return match ($this) {
            self::CACHE_CONTROL,
            self::CDN_CACHE_CONTROL,
            self::ETAG,
            self::EXPIRES,
            self::LAST_MODIFIED,
            self::IF_MATCH,
            self::IF_MODIFIED_SINCE,
            self::IF_NONE_MATCH,
            self::IF_RANGE,
            self::IF_UNMODIFIED_SINCE,
            self::PRAGMA,
            self::VARY => true,
            default    => false,
        };
    }
}
