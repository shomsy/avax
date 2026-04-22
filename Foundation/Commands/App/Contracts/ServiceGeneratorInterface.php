<?php

declare(strict_types=1);

namespace Avax\Commands\App\Contracts;

interface ServiceGeneratorInterface
{
    public function create(string $name) : void;
}
