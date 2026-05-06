<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\RunApplication\FrankenPhp;

use Closure;

class FrankenPhpRuntime
{
    public function __construct(private Closure $receiver, private Closure $sender)
    {
    }
}
