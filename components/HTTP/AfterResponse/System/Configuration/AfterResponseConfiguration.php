<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\AfterResponse\System\Configuration;

final readonly class AfterResponseConfiguration
{
    public function __construct(
        public bool $enabled = true,
        public int  $maxCallbacks = 50,
    ) {}
}
