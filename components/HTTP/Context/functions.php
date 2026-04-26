<?php

declare(strict_types=1);

use Avax\HTTP\Context\HttpContextInterface;

if (! function_exists(function: 'http_context')) {
    /**
     * Retrieve the HTTP Context instance.
     *
     * @return HttpContextInterface
     */
    function http_context() : HttpContextInterface
    {
        return app(abstract: HttpContextInterface::class);
    }
}
