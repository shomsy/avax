<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\ORM\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_CLASS)]
final readonly class Table
{
    public function __construct(public string $name)
    {
    }
}
