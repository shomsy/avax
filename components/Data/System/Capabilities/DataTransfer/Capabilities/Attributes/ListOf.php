<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\DataTransfer\Capabilities\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class ListOf
{
    /**
     * @param class-string $class
     */
    public function __construct(public string $class) {}

    /**
     * Legacy DTO list attributes used an of() method.
     *
     * @return class-string
     */
    public function of() : string
    {
        return $this->class;
    }
}
