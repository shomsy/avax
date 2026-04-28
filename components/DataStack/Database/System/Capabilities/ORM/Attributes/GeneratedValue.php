<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_PROPERTY)]
final readonly class GeneratedValue
{
    public function __construct(public string $strategy = 'auto') {}
}
