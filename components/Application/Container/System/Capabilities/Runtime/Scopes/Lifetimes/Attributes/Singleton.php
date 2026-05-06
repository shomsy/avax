<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\Lifetimes\Attributes;

use Attribute;

/**
 * Marks one class as shared across the container lifetime.
 */
#[Attribute(flags: Attribute::TARGET_CLASS)]
final class Singleton
{
}
