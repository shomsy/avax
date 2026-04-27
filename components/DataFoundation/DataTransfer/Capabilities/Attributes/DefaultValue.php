<?php

declare(strict_types=1);

namespace components\DataFoundation\DataTransfer\Capabilities\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class DefaultValue
{
    public function __construct(public mixed $value) {}
}
