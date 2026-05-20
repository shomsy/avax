<?php

declare(strict_types=1);

use Avax\Components\HTTP\Client\System\PublicSurface\HttpClient;
use Avax\Components\HTTP\Context\System\PublicSurface\HttpContextInterface;

if (! function_exists('http')) {
    function http() : HttpClient
    {
        return app(HttpClient::class);
    }
}

if (! function_exists('http_context')) {
    function http_context() : HttpContextInterface
    {
        return app(HttpContextInterface::class);
    }
}

// csrf_token/csrf_field/csrf_method are defined in
// components/HTTP/Security/System/PublicSurface/shortcuts.php (canonical owner).
