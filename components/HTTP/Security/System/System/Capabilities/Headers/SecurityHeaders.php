<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Security\System\System\Capabilities\Headers;

use Avax\Components\Security\System\PublicSurface\ResponseFormatter;

final readonly class SecurityHeaders
{
    public static function apply(ResponseFormatter $responseFormatter) : ResponseFormatter
    {
        return $responseFormatter
            ->withHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains')
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('X-Frame-Options', 'DENY')
            ->withHeader('X-XSS-Protection', '1; mode=block')
            ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->withHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    }

    public static function applyCsp(ResponseFormatter $responseFormatter, string $policy) : ResponseFormatter
    {
        return $responseFormatter->withHeader('Content-Security-Policy', $policy);
    }
}
