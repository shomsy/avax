<?php

declare(strict_types=1);

namespace Avax\DataLayer\System\Configuration;

final readonly class DataLayerConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
