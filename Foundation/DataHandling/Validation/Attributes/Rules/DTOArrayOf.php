<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;

/**
 * Attribute to automatically transform an array into an array of DTOs.
 *
 * Used for fields like: array<int, FieldDTO>
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class DTOArrayOf
{
    /**
     * @param class-string<AbstractDTO> $class Fully-qualified DTO class name
     */
    public function __construct(public string $class) {}

    /**
     * Transforms the input array into DTO instances
     *
     * @param array|null $value
     *
     * @return array<int, AbstractDTO>
     */
    public function apply(array|null $value) : array
    {
        return array_map(
            callback: fn(array $item) => new ($this->class)($item),
            array   : $value ?? []
        );
    }
}
