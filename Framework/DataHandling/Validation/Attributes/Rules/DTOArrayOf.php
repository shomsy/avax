<?php

declare(strict_types=1);

namespace Gemini\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Gemini\DataHandling\ObjectHandling\DTO\AbstractDTO;

/**
 * Attribute to automatically transform an array into an array of DTOs.
 *
 * Used for fields like: array<int, FieldDTO>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
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
            fn(array $item) => new ($this->class)($item),
            $value ?? []
        );
    }
}
