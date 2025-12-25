<?php

declare(strict_types=1);

namespace Avax\Container\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_CLASS)]
final class ServiceProvider {}