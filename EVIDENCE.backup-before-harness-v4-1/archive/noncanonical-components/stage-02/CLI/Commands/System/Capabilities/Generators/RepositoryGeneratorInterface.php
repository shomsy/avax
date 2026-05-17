<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Commands\System\Capabilities\Generators;

interface RepositoryGeneratorInterface
{
    public function create(string $tableName, string $entity) : void;
}
