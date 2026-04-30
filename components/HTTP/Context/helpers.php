<?php

declare(strict_types=1);

use Avax\Components\HTTP\Context\System\PublicSurface\HttpContextInterface;

if (! function_exists('http_context')) {
    function http_context(): HttpContextInterface
    {
        return app(HttpContextInterface::class);
    }
}
