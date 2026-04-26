<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\ORM\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_PROPERTY)]
final readonly class JoinColumn
{
    public function __construct(
        public string|null $name = null,
        public string      $referencedColumnName = 'id'
    ) {}
}
