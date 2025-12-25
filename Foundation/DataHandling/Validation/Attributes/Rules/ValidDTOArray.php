<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Attribute for validating an array of data transfer objects (DTOs).
 *
 * This attribute ensures that a property adheres to an array structure where all items
 * are valid DTOs inheriting from the specified target class (via `$dtoClass`).
 *
 * The validation process assumes that the target DTO class's constructor validates
 * its input automatically. Invalid data within the array will trigger a
 * ValidationException.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
final readonly class ValidDTOArray
{
    /**
     * The fully-qualified class name of the Data Transfer Object (DTO) we expect.
     *
     * @param string $dtoClass The DTO class name to validate instantiated items.
     */
    public function __construct(private string $dtoClass) {}

    /**
     * Validates the given value to ensure it is an array of valid DTOs.
     *
     * Checks the following conditions:
     * - The value must be an array.
     * - Each item in the array must be either an array or an object.
     * - An instance of the specified `$dtoClass` must be successfully created for each item.
     *
     * If any of the above conditions are violated, a ValidationException is thrown.
     *
     * @param mixed  $value    The value of the property to be validated.
     * @param string $property The name of the property being validated, used in exception messages.
     *
     * @throws ValidationException If validation fails.
     */
    public function validate(mixed $value, string $property) : void
    {
        // Ensure the provided value is of type array.
        if (! is_array(value: $value)) {
            throw new ValidationException(
                message: "Expected array of DTOs for {$property}"
            );
        }

        // Iterate through the array to validate each item.
        foreach ($value as $item) {
            // Ensure each item is either an array or an object.
            if (! is_array(value: $item) && ! is_object(value: $item)) {
                throw new ValidationException(
                    message: "Invalid item in {$property}, must be an array or object"
                );
            }

            // Attempt to instantiate the target DTO class with the item.
            // This assumes the DTO constructor validates its input.
            new $this->dtoClass($item);
        }
    }

    /**
     * Converts all elements of the array into instances of the specified DTO class.
     *
     * This method applies a transformation where each element of the input array
     * is passed to the constructor of the defined `$dtoClass`, returning a new array
     * of fully instantiated DTO objects.
     *
     * @param mixed $value The input array to transform.
     *
     * @return array<int, object> An array of DTO objects.
     *
     * @throws ValidationException If construction of any DTO fails.
     */
    public function apply(mixed $value) : array
    {
        return array_map(
            callback: fn($v) => new $this->dtoClass($v), // Instantiate DTO for each item
            array   : $value // Input array
        );
    }
}