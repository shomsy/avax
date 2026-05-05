<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Commands\System\Capabilities\Generators;

interface ControllerGeneratorInterface
{
    public function create(string $name) : void;
}