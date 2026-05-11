<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_PROPERTY)]
final readonly class Column
{
    public function __construct(
        public string|null $name = null,
        public string|null $type = null,
        public bool $nullable = false,
    ) {
    }
}
