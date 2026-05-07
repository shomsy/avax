<?php

declare(strict_types=1);

namespace Avax\Components\Application\Facade\System\Configuration;

final readonly class FacadeConfiguration
{
    public function __construct(
        public bool $autoResolve = true,
        public bool $lazyLoad = false,
    ) {}
}
