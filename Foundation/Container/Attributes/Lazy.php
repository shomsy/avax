<?php

declare(strict_types=1);

namespace Avax\Container\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final readonly class Lazy {}