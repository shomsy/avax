<?php

declare(strict_types=1);

namespace Avax\Commands\App\Contracts;

interface RepositoryGeneratorInterface
{
    public function create(string $tableName, string $entity) : void;
}
