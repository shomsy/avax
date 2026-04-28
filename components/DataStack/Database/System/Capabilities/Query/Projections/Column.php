<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Projections;

use Attribute;

#[Attribute(flags: Attribute::TARGET_PROPERTY)]
final class Column
{
    public function __construct(
        public readonly string|null $name = null,
        public readonly string|null $type = null
    ) {}
}
