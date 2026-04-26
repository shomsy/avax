<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Capabilities\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class MapFrom
{
    public function __construct(public string $name) {}
}
