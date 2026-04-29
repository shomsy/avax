<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware\System\Flows\RunMiddlewarePipeline;

use Avax\Components\HTTP\Middleware\System\PublicSurface\MiddlewareInterface;

final class ResolveNextMiddleware
{
    public function resolve(array $stack, int $index): ?MiddlewareInterface
    {
        return $stack[$index] ?? null;
    }
}
