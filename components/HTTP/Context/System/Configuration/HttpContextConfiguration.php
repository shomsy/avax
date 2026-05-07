<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Context\System\Configuration;

final readonly class HttpContextConfiguration
{
    public function __construct(
        public bool $autoStart = true,
        public int  $maxItems = 1000,
    ) {}
}
