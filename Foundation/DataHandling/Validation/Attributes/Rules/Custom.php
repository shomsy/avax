<?php

declare(strict_types=1);

/**
 * The Custom attribute class allows for custom validation logic to be applied
 * to object properties. This is particularly useful for defining property-specific
 * validation rules that don't fit standard validation patterns.
 *
 * - `Attribute::TARGET_PROPERTY` ensures this attribute is applied to properties.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Closure;
use Avax\Exceptions\ValidationException;

/**
 * Indicates that this attribute is targeting a property and is immutable.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class Custom
{
    public function __construct(private Closure $callback) {}

    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        $isValid = ($this->callback)($value);
        if (! $isValid) {
            throw new ValidationException(message: $property . ' is invalid according to custom rule.');
        }
    }
}
