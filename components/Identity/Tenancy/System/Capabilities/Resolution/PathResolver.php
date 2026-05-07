<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Resolution;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;

final readonly class PathResolver
{
    public static function resolve(RequestInterface $request) : ?string
    {
        $path  = $request->getUri()->getPath();
        $parts = explode('/', $path);

        if (count($parts) >= 2 && $parts[1] === 'tenants') {
            return $parts[2] ?? null;
        }

        return null;
    }
}
