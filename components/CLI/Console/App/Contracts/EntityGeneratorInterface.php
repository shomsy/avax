<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\App\Contracts;

interface EntityGeneratorInterface
{
    /**
     * @param array<int, array{name: string, type: string}> $fields
     */
    public function create(string $tableName, array $fields = []) : void;
}
