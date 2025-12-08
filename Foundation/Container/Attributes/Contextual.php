<?php

declare(strict_types=1);

namespace Avax\Container\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class Contextual
{
    public function __construct(
        public string      $target,
        public string|null $dependency = null
    ) {}
}