<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\RunApplication\RoadRunner;

use Closure;

class RoadRunnerRuntime
{
    public function __construct(private Closure $receiver, private Closure $sender) {}
}
