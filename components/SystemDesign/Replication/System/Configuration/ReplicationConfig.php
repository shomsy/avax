<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Replication\System\Configuration;

final readonly class ReplicationConfig
{
    public function __construct(
        public bool $enabled = true,
    )
    {
    }
}
