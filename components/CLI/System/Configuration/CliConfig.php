<?php

declare(strict_types=1);

namespace Avax\Components\CLI\System\Configuration;

final readonly class CliConfig
{
    public function __construct(
        public string $name = 'avax',
    )
    {
    }
}
