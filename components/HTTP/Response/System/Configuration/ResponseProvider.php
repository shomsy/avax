<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Configuration;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentProviderInterface;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;

final class ResponseProvider implements ComponentProviderInterface
{
    public static function name() : string
    {
        return 'response';
    }

    public function boot(RuntimeInterface $runtime) : void
    {
        $this->register(componentRegistry: $runtime->components());
    }

    public function register(ComponentRegistry $componentRegistry) : void
    {
        // Registration logic
    }
}
