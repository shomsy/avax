<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\RunApplication\Workerman;

use Closure;

class WorkermanRuntime
{
    public function __construct(private Closure $receiver, private Closure $sender)
    {
    }
}
