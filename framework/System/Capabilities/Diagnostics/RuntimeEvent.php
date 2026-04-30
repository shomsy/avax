<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Diagnostics;

final class RuntimeEvent
{
    public function __construct(
        public readonly string $name,
        public readonly float $timestamp,
        public readonly array $data = [],
    ) {
    }
}
