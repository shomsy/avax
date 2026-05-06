<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Attributes;

use Attribute;

/**
 * Marks one parameter as runtime input instead of a container dependency.
 */
#[Attribute(flags: Attribute::TARGET_PARAMETER)]
final class RuntimeInput
{
    public function __construct(public ?string $name = null)
    {
    }
}
