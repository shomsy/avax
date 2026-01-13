<?php

declare(strict_types=1);

/**
 * FileException ensures that a given property's value is a valid file.
 *
 * This attribute is applied to properties of a Data Transfer Object (DTO)
 * to enforce file validation rules. The class uses PHP's Attribute feature,
 * which allows adding metadata to classes, methods, properties, and more.
 *
 * The class operates under the assumption that file validation is critical
 * for the correctness and security of the business logic in the application
 * where it's used.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Custom exception for file validation errors.
 *
 * This class is used as an attribute to indicate that the associated property
 * should be validated to ensure it is a file. The rationale behind this custom
 * exception is to provide a more specific and meaningful error when validation fails.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class FileException
{
    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property): void
    {
        if (! is_file(filename: $value)) {
            throw new ValidationException(message: $property.' must be a file.');
        }
    }
}
