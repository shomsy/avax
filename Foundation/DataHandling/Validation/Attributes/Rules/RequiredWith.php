<?php

declare(strict_types=1);

/**
 * Attribute class enforcing a "required with" validation rule.
 *
 * This attribute ensures that a given property must have a value if the specified fields are present in the provided
 * data array. The class and its methods help enforce specific business rules where data interdependencies require
 * conditional validations.
 *
 * Attribute is set to TARGET_PROPERTY for use with class properties.
 * The class is marked as readonly as it doesn't require modification after instantiation.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Attribute class for validating that a property is required if specified sibling properties are present.
 *
 * The readonly modifier ensures that the $fields property is immutable, providing safety by preventing accidental
 * changes.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class RequiredWith
{
    public function __construct(private array $fields) {}

    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, array $data, string $property): void
    {
        foreach ($this->fields as $field) {
            if (isset($data[$field]) && empty($value)) {
                throw new ValidationException(
                    message: sprintf('%s is required when %s is present.', $property, $field),
                );
            }
        }
    }
}
