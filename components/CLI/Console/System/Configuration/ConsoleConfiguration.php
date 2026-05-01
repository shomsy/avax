<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Configuration;

final readonly class ConsoleConfiguration
{
    public function __construct(
        public string $name = 'Avax Console',
        public string $version = '1.0.0',
        public bool   $interactive = true,
    ) {}
}
