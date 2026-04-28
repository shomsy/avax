<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\App\Contracts;

interface RepositoryGeneratorInterface
{
    public function create(string $tableName, string $entity) : void;
}
