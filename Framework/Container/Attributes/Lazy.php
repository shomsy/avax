<?php

declare(strict_types=1);

namespace Gemini\Container\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final readonly class Lazy {}