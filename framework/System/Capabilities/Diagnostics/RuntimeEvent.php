<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Diagnostics;

final readonly class RuntimeEvent
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public string $name,
        public float  $timestamp,
        public array  $data = [],
    ) {}
}
