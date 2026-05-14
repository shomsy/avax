<?php

declare(strict_types=1);

use Avax\Components\HTTP\Client\System\PublicSurface\HttpClient;
use Avax\Components\HTTP\Context\System\PublicSurface\HttpContextInterface;
use Avax\Components\HTTP\Security\System\PublicSurface\Security;

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

if (! function_exists('csrf_token')) {
    function csrf_token() : string
    {
        // This assumes a global app() helper exists as per backup
        return app(Security::class)->csrfToken();
    }
}
