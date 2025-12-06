<?php

declare(strict_types=1);

/**
 * The Different validation attribute is used to enforce that a property must have a different value
 * from another specified property within the same data context.
 *
 * This is a read-only attribute applied to a class property, ensuring immutability after instantiation.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * The Different class is an immutable validator to ensure that a given property in a dataset
 * differs from another specified property. It is intended to be used as an attribute for data validation.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class Different
{
    public function __construct(private string $field) {}

    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, array $data, string $property) : void
    {
        if ($value === ($data[$this->field] ?? null)) {
            throw new ValidationException(message: sprintf('%s must be different from %s.', $property, $this->field));
        }
    }
}
