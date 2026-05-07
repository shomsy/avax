<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\DumpDebugger\System\Configuration;

final readonly class DumpDebuggerConfiguration
{
    public function __construct(
        public string $defaultFormat = 'html',
        public int    $maxDepth = 5,
        public bool   $enabled = true,
    ) {}
}
