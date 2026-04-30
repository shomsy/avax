<?php

declare(strict_types=1);

use Avax\Components\HTTP\Client\System\PublicSurface\HttpClient;

if (! function_exists('http')) {
    function http(): HttpClient
    {
        return app(HttpClient::class);
    }
}
