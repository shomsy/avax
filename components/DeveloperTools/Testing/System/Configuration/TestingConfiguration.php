<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Testing\System\Configuration;

final readonly class TestingConfiguration
{
    public function __construct(
        public bool $strictMode = true,
        public bool $failOnBreaking = true,
    ) {}
}
