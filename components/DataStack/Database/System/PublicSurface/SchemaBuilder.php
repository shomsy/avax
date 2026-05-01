<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\PublicSurface;

final class SchemaBuilder
{
    public function __construct(
        private readonly Database $database,
    ) {}

    public function create(string $table, callable $definition) : void {}

    public function drop(string $table) : void {}

    public function hasTable(string $table): bool
    {
        return false;
    }
}
