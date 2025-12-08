<?php

declare(strict_types=1);

namespace Avax\Container\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class Tag
{
    public function __construct(public string $name) {}
}