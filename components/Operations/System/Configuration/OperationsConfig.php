<?php

declare(strict_types=1);

namespace Avax\Components\Operations\System\Configuration;

final readonly class OperationsConfig
{
    public function __construct(
        public bool $enabled = true,
    ) {
    }
}
