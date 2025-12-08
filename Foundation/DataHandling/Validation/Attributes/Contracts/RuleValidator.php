<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Contracts;

use Avax\Exceptions\ValidationException;

/**
 * Interface RuleValidator
 *
 * A contract for implementing custom validation rules for a Data Transfer Object (DTO).
 * This interface enforces a consistent structure and ensures flexibility
 * when implementing reusable and testable validation logic.
 */
interface RuleValidator
{
    /**
     * Validates the input value for a specific property of a Data Transfer Object (DTO).
     *
     * @param mixed  $value    The value to be validated.
     *                         It can be of any data type and represents the value assigned to the DTO property.
     * @param array  $data     The complete data array representing the DTO.
     *                         This allows access to other properties of the DTO during validation, enabling
     *                         advanced validation logic that involves relationships between properties.
     * @param string $property The name of the DTO property being validated.
     *                         This parameter identifies which specific property the $value represents.
     *
     * @throws ValidationException If the validation fails.
     *                             The exception provides details of the validation failure, enabling the caller
     *                             to handle validation errors appropriately, such as returning user-friendly error
     *                             messages or logging the failure for debugging purposes.
     */
    public function validate(mixed $value, array $data, string $property) : void;
}