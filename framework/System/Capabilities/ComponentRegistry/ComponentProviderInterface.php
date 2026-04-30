<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ComponentRegistry;

use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;

interface ComponentProviderInterface
{
    public function name() : string;

    public function boot(RuntimeInterface $runtime) : void;
}
