<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Configuration;

use Avax\Components\HTTP\Middleware\System\Capabilities\Pipeline\MiddlewarePipeline;
use Avax\Components\HTTP\System\PublicSurface\Http;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentProviderInterface;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;

final class HttpProvider implements ComponentProviderInterface
{
    public function register(ComponentRegistry $registry): void
    {
        $registry->single('http', static fn () => new Http(
            $registry->get('router'),
            new MiddlewarePipeline(), // Should be populated with global middlewares
        ));
    }
}
