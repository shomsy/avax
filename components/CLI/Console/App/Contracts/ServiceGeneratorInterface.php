<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\App\Contracts;

interface ServiceGeneratorInterface
{
    public function create(string $name) : void;
}
