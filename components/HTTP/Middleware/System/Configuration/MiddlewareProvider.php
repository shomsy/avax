<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware\System\Configuration;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentProviderInterface;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;

final class MiddlewareProvider implements ComponentProviderInterface
{
    public static function name() : string
    {
        return 'http.middleware';
    }

    public function boot(RuntimeInterface $runtime) : void {}

    public function register(ComponentRegistry $componentRegistry) : void
    {
        // Registration logic
    }
}
