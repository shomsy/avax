<?php

declare(strict_types=1);

/**
 * Represents a validation rule that asserts a property must end with one of the specified suffixes.
 *
 * This attribute is exclusively designed to be used on class properties, ensuring that the designated
 * property ends with one of the provided suffixes during validation.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * The EndsWith class is used to validate that a given value ends with one of the specified suffixes.
 *
 * This is particularly useful for ensuring values like file extensions, URLs, or other string properties
 * meet specific criteria. Instead of marking individual properties with multiple attributes, this class
 * allows for a centralized validation logic.
 *
 * ## Why Readonly:
 * The readonly class modifier is used here to ensure immutability of class instances. Once instantiated,
 * the suffixes cannot be altered, providing consistent behaviour throughout the lifetime of the object.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class EndsWith
{
    public function __construct(private array $suffixes) {}

    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property): void
    {
        foreach ($this->suffixes as $suffix) {
            if (str_ends_with(haystack: (string) $value, needle: (string) $suffix)) {
                return;
            }
        }

        throw new ValidationException(
            message: $property.' must end with one of the following: '.implode(separator: ', ', array: $this->suffixes),
        );
    }
}
