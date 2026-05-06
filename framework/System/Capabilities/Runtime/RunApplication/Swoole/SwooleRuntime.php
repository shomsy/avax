<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\RunApplication\Swoole;

use Closure;

class SwooleRuntime
{
    public function __construct(private Closure $receiver, private Closure $sender)
    {
    }
}
