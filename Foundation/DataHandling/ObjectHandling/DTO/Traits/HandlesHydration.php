<?php

declare(strict_types=1);

namespace Avax\DataHandling\ObjectHandling\DTO\Traits;

use Avax\DataHandling\ObjectHandling\DTO\DTOValidationException;
use InvalidArgumentException;
use ReflectionProperty;
use Throwable;

/**
 * The `HandlesHydration` trait provides advanced hydration logic for DTO objects.
 *
 * It operates by using reflection to dynamically populate object properties with
 * provided raw data while performing type casting, validation, and handling attributes.
 * This ensures that the hydrated object adheres to the defined structure and constraints.
 */
trait HandlesHydration
{
    /**
     * Method responsible for dynamic hydration of the object with raw input data.
     *
     * The method processes each public field of the DTO using reflection, applying
     * type validation, attribute-based transformations, and error handling for invalid data.
     *
     * @param array<string, mixed> $data An associative array of input data for hydration,
     *                                   where keys correspond to public property names
     *                                   and values represent their respective input values.
     *
     * @throws DTOValidationException Thrown if one or more fields fail validation during hydration.
     * @throws \ReflectionException   Raised when an error occurs in accessing reflective metadata for the class.
     */
    public function hydrateFrom(array $data) : void
    {
        $errors        = [];
        $simpleMessage = null;

        foreach ($this->reflectPublicFields() as $meta) {
            try {
                $this->hydrateField(
                    name      : $meta->name,
                    property  : $meta->property,
                    attributes: $meta->attributes,
                    data      : $data
                );
            } catch (Throwable $e) {
                $errors[$meta->name] = $this->formatHydrationError(
                    fieldName: $meta->name,
                    e        : $e
                );

                $simpleMessage = $e->getMessage();
            }
        }

        if (! empty($errors)) {
            logger()->warning(
                message: 'DTO hydration failed - ' . $simpleMessage,
                context: ['errors' => $errors]
            );

            throw new DTOValidationException(
                message: 'DTO hydration failed - ' . $simpleMessage,
                errors : $errors
            );
        }
    }

    /**
     * Hydrates a single field of the DTO by casting, validating, and assigning the value.
     *
     * @param string             $name
     * @param ReflectionProperty $property
     * @param array              $attributes
     * @param array              $data
     */
    protected function hydrateField(
        string             $name,
        ReflectionProperty $property,
        array              $attributes,
        array              $data
    ) : void {
        if (! array_key_exists($name, $data)) {
            $this->handleMissingField(name: $name, property: $property);

            return;
        }

        $rawValue = $data[$name];

        // 🔁 STEP 1: Type casting FIRST
        $resolvedValue = $this->castToExpectedType(
            property: $property,
            value   : $rawValue
        );

        // ✅ STEP 2: Validate AFTER casting
        $this->validateField(
            fieldName : $name,
            value     : $resolvedValue,
            attributes: $attributes
        );

        // ✅ STEP 3: Set property after validation
        $this->$name = $resolvedValue;
    }

    /**
     * Handles scenarios where a field is missing during hydration by
     * either assigning a default value, setting it to `null` if nullable,
     * or throwing an exception for required fields.
     *
     * @param string             $name      The name of the missing property in the DTO.
     * @param ReflectionProperty $property  Reflective metadata for the missing property,
     *                                      used to inspect its type and default value.
     *
     * @throws InvalidArgumentException If the field is required but no value or default is provided.
     */
    protected function handleMissingField(string $name, ReflectionProperty $property) : void
    {
        // Check if the property explicitly allows null values using type reflection.
        if ($this->isPropertyNullable(property: $property)) {
            // Assign null to the property if it is nullable.
            $this->$name = null;

            return;
        }

        // Check if the property has a default value defined in the class.
        if ($property->hasDefaultValue()) {
            // Retrieve and assign the default value to the property if available.
            $this->$name = $property->getDefaultValue();

            return;
        }

        // Log a warning indicating that a required field is missing during hydration.
        logger()->warning(
            message: 'Missing required field: ' . $name, // Descriptive message for the log entry.
            context: ['class' => static::class] // Include the class name for debugging context.
        );

        // Throw an exception if the field is required and no value or default is provided.
        throw new InvalidArgumentException(
            message: "Missing required field: {$name}" // Provide a clear error message.
        );
    }

    /**
     * Validates the resolved value with all assigned attributes.
     *
     * @param string $fieldName
     * @param mixed  $value
     * @param array  $attributes
     */
    private function validateField(string $fieldName, mixed $value, array $attributes) : void
    {
        foreach ($attributes as $attribute) {
            if (method_exists($attribute, 'validate')) {
                $attribute->validate(
                    value   : $value,
                    property: $fieldName
                );
            }
        }
    }

    /**
     * Formats a hydration error for detailed exception reporting.
     *
     * @param string    $fieldName
     * @param Throwable $e
     *
     * @return string
     */
    private function formatHydrationError(string $fieldName, Throwable $e) : string
    {
        return sprintf(
            '%s → Field "%s": %s',
            static::class,
            $fieldName,
            $e->getMessage()
        );
    }
}
