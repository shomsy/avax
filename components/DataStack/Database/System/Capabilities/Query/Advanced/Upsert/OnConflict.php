<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Advanced\Upsert;

final readonly class OnConflict
{
    public function __construct(
        public array $columns,
        public array $updateColumns = [],
        public bool $doNothing = false,
    ) {}

    public static function columns(array $columns, array $updateColumns = []): self
    {
        return new self(columns: $columns, updateColumns: $updateColumns);
    }

    public static function doNothing(array $columns): self
    {
        return new self(columns: $columns, doNothing: true);
    }
}
