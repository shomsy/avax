<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Execution\Injection\Attributes;

use Attribute;

/**
 * Marks one property, method, or parameter for container injection.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
final class Inject
{
    public function __construct(
        public string|null $abstract = null
    ) {}
}
