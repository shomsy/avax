<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Commands\System\Capabilities\Generators;

interface EntityGeneratorInterface
{
    public function create(string $tableName, array $fields = []): void;
}