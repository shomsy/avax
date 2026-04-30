<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Foundation\Time;

use Override;

final class SystemClock implements Clock
{
    #[Override]
    public function now() : Timestamp
    {
        return Timestamp::now();
    }
}
