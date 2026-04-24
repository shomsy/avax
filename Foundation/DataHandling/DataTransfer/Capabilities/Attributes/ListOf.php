<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Capabilities\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class ListOf
{
    /**
     * @param class-string $class
     */
    public function __construct(public string $class) {}

    /**
     * Legacy DTO list attributes used an of() method. Keeping it here makes the new
     * attribute readable by old extension points without preserving the old trait runtime.
     *
     * @return class-string
     */
    public function of() : string
    {
        return $this->class;
    }
}
