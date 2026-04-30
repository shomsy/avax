<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Commands\System\Capabilities\Generators;

interface ControllerGeneratorInterface
{
    public function create(string $name) : void;
}

interface EntityGeneratorInterface
{
    public function create(string $tableName, array $fields = []) : void;
}

interface RepositoryGeneratorInterface
{
    public function create(string $tableName, string $entity) : void;
}

interface ServiceGeneratorInterface
{
    public function create(string $name) : void;
}
