<?php

declare(strict_types=1);

namespace Avax\API\System\Configuration;

final readonly class ApiConfiguration
{
    public function __construct(
        public bool   $enabled = true,
        public string $version = '1.0.0',
        public array  $options = [],
    )
    {
    }

    public static function defaults(): self
    {
        return new self();
    }
}