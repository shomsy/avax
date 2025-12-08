<?php

declare(strict_types=1);

/**
 * The RequiredWithout attribute marks a property as required only if certain other fields are not present.
 * This is useful for conditional validation scenarios where the presence of a property is dependent on the absence of
 * other properties.
 *
 * This attribute is intended to be applied to class properties (Target: PROPERTY).
 * It leverages PHP 8's attribute syntax to integrate seamlessly with the language's validation infrastructure.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Class RequiredWithout
 *
 * Attribute class to enforce the requirement of a property being non-empty unless certain other properties are present
 * in data.
 *
 * - The use of readonly ensures immutability, making fields immutable after instantiation.
 * - The __construct function takes an array of fields to check against, ensuring robustness and flexibility.
 * - The validate method includes complex business logic following a specific validation rule, demanding an explanation
 * for future maintainability.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class RequiredWithout
{
    public function __construct(private array $fields) {}

    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, array $data, string $property) : void
    {
        foreach ($this->fields as $field) {
            if (! isset($data[$field]) && empty($value)) {
                throw new ValidationException(
                    message: sprintf(
                                 '%s is required when %s is not present.',
                                 $property,
                                 $field,
                             ),
                );
            }
        }
    }
}
