<?php

declare(strict_types=1); // Strict type declarations to enforce type safety and ensure predictable behavior of code.

namespace Avax\DataHandling\ObjectHandling\DTO;

use InvalidArgumentException;

/**
 * DTOValidationException
 *
 * This final exception class is specifically designed to encapsulate multiple
 * validation errors when dealing with Data Transfer Objects (DTOs).
 *
 * As part of Domain-Driven Design (DDD), this exception helps to clearly define
 * validation-related errors, thereby enhancing the domain layer's adherence to constraints
 * and encapsulating the behavior required to handle such errors.
 *
 * Extends:
 *  - InvalidArgumentException: This base exception aligns with the concept of invalid
 *    arguments being passed to a DTO during validation, enhancing semantic meaning.
 */
final class DTOValidationException extends InvalidArgumentException
{
    /**
     * A collection of validation errors.
     *
     * This property holds an associative array containing validation error messages,
     * where the key represents the invalid field name, and the value represents
     * the reason or detailed validation error message.
     *
     * The `readonly` contract ensures immutability of this object property after construction,
     * adhering to clean code principles for simple and predictable objects.
     *
     * @var array<string, string> An associative array where the keys are field names,
     *                            and the values are validation error messages.
     */
    public readonly array $errors;

    /**
     * Constructs a new DTOValidationException.
     *
     * Leverages constructor promotion for leaner and more expressive class construction
     * while ensuring appropriate validation messages and errors are encapsulated.
     *
     * @param string                $message A detailed exception message providing context about the DTO validation
     *                                       failure.
     * @param array<string, string> $errors  Associative array of validation errors, with keys as field names
     *                                       and values as corresponding messages explaining the validation failure.
     */
    public function __construct(
        string $message,
        array  $errors,
    ) {
        $formattedErrors = [];

        foreach ($errors as $field => $errorMsg) {
            $formattedErrors[] = sprintf('%s: %s', $field, $errorMsg);
        }

        $message .= "\n" . implode("\n", $formattedErrors);

        parent::__construct(message: $message);
        $this->errors = $errors;
    }


    public function jsonSerialize() : array
    {
        return [
            'error'  => $this->getMessage(),
            'fields' => $this->getErrors(),
        ];
    }

    /**
     * Retrieves the collection of validation errors.
     *
     * This method provides read-only access to the `errors` property containing detailed
     * validation error information for the failed DTO attributes or fields.
     *
     * Keeping this method focused and simple aligns with the principles of clean code
     * by facilitating immutability and enforcing predictable behavior.
     *
     * @return array<string, string> Returns an associative array of validation errors, where the
     *                               keys represent the invalid fields or attributes, and the values
     *                               detail the validation issues.
     */
    public function getErrors() : array
    {
        // Return the immutably defined validation error details to the caller.
        return $this->errors;
    }

}