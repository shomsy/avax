<?php

declare(strict_types=1);

/**
 * Attribute class used to enforce that the value of the decorated property
 * must match the value of another specified property within the same data context.
 *
 * This attribute should be applied to properties within a DTO to ensure
 * that certain fields have equal values, which is helpful for tasks such as
 * confirming password or email fields.
 *
 * Using the readonly class guarantees immutability once instantiated, ensuring data consistency.
 */

namespace Gemini\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Gemini\Exceptions\ValidationException;

/**
 * This class defines a validation rule that ensures a given property value is the same as another specified property
 * value. It is an immutable class, signified by the 'readonly' keyword, meaning its state cannot be altered after
 * instantiation.
 *
 * The primary use case is validation scenarios where fields need to have matching values, such as password
 * confirmation fields.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class Same
{
    public function __construct(private string $field) {}

    /**
     * @throws \Gemini\Exceptions\ValidationException
     */
    public function validate(mixed $value, array $data, string $property) : void
    {
        if ($value !== ($data[$this->field] ?? null)) {
            throw new ValidationException(message: sprintf('%s must be the same as %s.', $property, $this->field));
        }
    }
}
