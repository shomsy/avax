<?php

declare(strict_types=1);

namespace Gemini\Container\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Singleton {}