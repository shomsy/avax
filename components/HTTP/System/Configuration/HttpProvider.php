<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\System\Configuration;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentProviderInterface;
use Avax\Components\HTTP\System\PublicSurface\Http;
use Avax\Components\HTTP\Middleware\System\Capabilities\Pipeline\MiddlewarePipeline;

final class HttpProvider implements ComponentProviderInterface
{
    public function register(ComponentRegistry $registry): void
    {
        $registry->single('http', function() use ($registry) {
            return new Http(
                $registry->get('router'),
                new MiddlewarePipeline() // Should be populated with global middlewares
            );
        });
    }
}
