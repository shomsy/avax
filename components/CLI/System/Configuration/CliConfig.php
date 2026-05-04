<?php

declare(strict_types=1);

namespace Avax\CLI\System\Configuration;

final readonly class CliConfig
{
    public function __construct(
        public string $name = 'avax',
    )
    {
    }
}
