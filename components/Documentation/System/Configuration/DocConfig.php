<?php

declare(strict_types=1);

namespace Avax\Documentation\System\Configuration;

final readonly class DocConfig
{
    public function __construct(
        public string $output = 'docs/',
    )
    {
    }
}
