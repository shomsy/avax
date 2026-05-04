<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Commands\System\Capabilities\Generators;

interface CapabilityGeneratorInterface
{
    public function create(string $name): void;
}