<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Advanced\Upsert;

final class OnConflict
{
    public function __construct(
        public readonly array $columns,
        public readonly array $updateColumns = [],
        public readonly bool $doNothing = false,
    ) {}

    public static function columns(array $columns, array $updateColumns = []) : self
    {
        return new self(columns: $columns, updateColumns: $updateColumns);
    }

    public static function doNothing(array $columns) : self
    {
        return new self(columns: $columns, doNothing: true);
    }
}
