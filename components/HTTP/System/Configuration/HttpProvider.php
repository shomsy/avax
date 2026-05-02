<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Configuration;

use Avax\Components\HTTP\Middleware\System\Capabilities\Pipeline\MiddlewarePipeline;
use Avax\Components\HTTP\System\PublicSurface\Http;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentProviderInterface;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;

final class HttpProvider implements ComponentProviderInterface
{
    public function register(ComponentRegistry $componentRegistry) : void
    {
        $componentRegistry->single('http', static fn () : Http => new Http(
            $componentRegistry->get('router'),
            new MiddlewarePipeline(), // Should be populated with global middlewares
        ));
    }
}
