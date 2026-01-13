<?php

declare(strict_types=1);

namespace Avax\DataHandling\ObjectHandling\DTO\Support;

use Avax\DataHandling\ObjectHandling\DTO\DTOValidationException;
use Avax\DataHandling\ObjectHandling\DTO\Traits\CastsTypes;
use Avax\DataHandling\ObjectHandling\DTO\Traits\HandlesAttributes;
use Avax\DataHandling\ObjectHandling\DTO\Traits\InspectsProperties;
use Avax\DataHandling\ObjectHandling\DTO\Traits\Serialization;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Throwable;

/**
 * A utility class for handling deep reflection-based operations on DTOs.
 *
 * This class provides functionality for manipulating, inspecting, and hydrating
 * Data Transfer Objects (DTOs) through reflection, while maintaining domain and type safety.
 *
 * ### Key Responsibilities:
 * - Hydration of DTO properties with strict validation and error reporting.
 * - Reflection and inspection of public properties and their metadata.
 * - Handling complex business rules through attributes and type casting.
 */
final class Reflector
{
    use CastsTypes;

    use HandlesAttributes;
    /**
     * Use traits that modularize reflection-based behaviors.
     * - `InspectsProperties`: Adds the ability to inspect DTO object's properties.
     * - `CastsTypes`: Handles casting raw values to expected types as part of hydration.
     * - `HandlesAttributes`: Processes and applies custom attribute-based rules on properties.
     * - `Serialization`: Offers serialization support for the DTO.
     */
    use InspectsProperties;
    use Serialization;

    /**
     * The target object being reflected and operated on.
     *
     * This object is the primary reference for all reflection-based operations
     * such as property inspection, hydration, and serialization.
     *
     * @var object The DTO or object being managed by this reflector.
     */
    private object $target;

    /**
     * Constructs a Reflector instance and initializes it with a target object.
     *
     * Follows constructor promotion for lean and expressive initialization.
     *
     * @param  object  $target  The target object for reflection and operations.
     */
    public function __construct(object $target)
    {
        $this->target = $target;
    }

    /**
     * Creates a Reflector instance for a specific object instance.
     *
     * This factory method enables a fluent and semantic API for initializing
     * a Reflector from an existing object.
     *
     * @param  object  $instance  The object instance being wrapped by the reflector.
     * @return self Returns a new Reflector instance.
     */
    public static function fromInstance(object $instance): self
    {
        return new self(target: $instance);
    }

    /**
     * Creates a Reflector instance for a given class name.
     *
     * Uses `ReflectionClass` to instantiate the object without calling its constructor,
     * allowing flexibility for reflection-based object construction and hydration.
     *
     * @param  string  $className  The fully qualified class name of the target object.
     * @return self Returns a new Reflector instance wrapping the created object.
     *
     * @throws ReflectionException If the provided class does not exist or cannot be instantiated.
     */
    public static function fromClass(string $className): self
    {
        return new self(target: (new ReflectionClass(objectOrClass: $className))->newInstanceWithoutConstructor());
    }

    /**
     * Hydrates the target object with the provided raw data.
     *
     * Iterates over the public properties of the target object and applies the
     * given raw data to each property. Attributes and type safety rules are
     * respected during the process, ensuring that all DTO constraints are enforced.
     *
     * @param  array<string, mixed>  $data  An associative array mapping property names
     *                                      to their corresponding values.
     *
     * @throws DTOValidationException If hydration fails due to validation or type casting errors.
     * @throws ReflectionException If reflection operations encounter an issue.
     */
    public function hydrate(array $data): void
    {
        // Initialize an empty array to collect errors during the hydration process.
        $errors = [];

        // Iterate through all public fields of the target object.
        foreach ($this->reflectPublicFields() as $meta) {
            try {
                // Attempt to hydrate the given field using the metadata and data provided.
                $this->hydrateField(
                    name      : $meta->name,
                    property  : $meta->property,
                    attributes: $meta->attributes,
                    data      : $data
                );
            } catch (Throwable $exception) {
                // Capture and format any errors that occur during hydration.
                $errors[$meta->name] = $this->formatHydrationError(
                    fieldName: $meta->name,
                    exception: $exception
                );
            }
        }

        // If any errors occurred during hydration, throw a validation exception.
        if (! empty($errors)) {
            throw new DTOValidationException(
                message: 'DTO hydration failed.',
                errors : $errors
            );
        }
    }

    /**
     * Populates a specific field of the target object with a value from the data array.
     *
     * The method validates the presence of the field in the raw data, handles type casting,
     * and applies any field-specific attributes before assigning the final value.
     *
     * @param  string  $name  The name of the property being hydrated.
     * @param  ReflectionProperty  $property  The reflection of the target property.
     * @param  array  $attributes  An array of attributes applied to the property.
     * @param  array  $data  The raw input data used for hydration.
     */
    private function hydrateField(
        string $name,
        ReflectionProperty $property,
        array $attributes,
        array $data
    ): void {
        // If the field is not present in the data array, handle it as missing.
        if (! array_key_exists(key: $name, array: $data)) {
            $this->handleMissingField(name: $name, property: $property);

            return;
        }

        // Extract the raw value corresponding to the field.
        $rawValue = $data[$name];

        // Cast the raw value to the expected type of the property.
        $resolvedValue = $this->castToExpectedType(property: $property, value: $rawValue);

        // Apply attribute-specific rules or transformations to the field value.
        $resolvedValue = $this->applyFieldAttributes(
            fieldName : $name,
            value     : $resolvedValue,
            attributes: $attributes
        );

        // Assign the resolved value to the corresponding property of the target object.
        $this->target->{$name} = $resolvedValue;
    }

    /**
     * Handles cases where required data for a field is missing.
     *
     * This method sets default values or null based on the property's attributes
     * or throws an exception if the property is mandatory and cannot be resolved.
     *
     * @param  string  $name  The name of the missing property.
     * @param  ReflectionProperty  $property  The reflection of the target property.
     *
     * @throws InvalidArgumentException If no suitable value is found for the missing property.
     */
    private function handleMissingField(string $name, ReflectionProperty $property): void
    {
        // If the property is nullable, assign a null value to the field.
        if ($this->isPropertyNullable(property: $property)) {
            $this->target->{$name} = null;

            return;
        }

        // If the property has a default value, assign it to the field.
        if ($property->hasDefaultValue()) {
            $this->target->{$name} = $property->getDefaultValue();

            return;
        }

        // Throw an exception when no suitable value is available for the property.
        throw new InvalidArgumentException(message: "Missing required field: {$name}");
    }

    /**
     * Formats detailed error messages for failed hydration of a single field.
     *
     * @param  string  $fieldName  The name of the field where hydration failed.
     * @param  Throwable  $exception  The exception that occurred during hydration.
     * @return string Returns a string describing the error with the field's name and exception message.
     */
    private function formatHydrationError(string $fieldName, Throwable $exception): string
    {
        return sprintf(
            '%s → Field "%s": %s',
            $this->target::class,
            $fieldName,
            $exception->getMessage()
        );
    }

    /**
     * Retrieves the target object being operated on by the Reflector.
     *
     * @return object The target object.
     */
    public function getTarget(): object
    {
        return $this->target;
    }

    /**
     * Converts the public properties of a target object into a schema-friendly array format.
     *
     * This method inspects the metadata of all public fields in the object,
     * including property type, nullability, and attributes,
     * and formats this data into an array representation.
     *
     * @return array An array representing the schema of the object's public fields.
     *
     * @throws \ReflectionException If reflection operations encounter an error.
     */
    public function toSchema(): array
    {
        // Apply a transformation to each metadata entry from reflectPublicFields().
        // The resulting array will contain a schema representation for each public property.
        return array_map(
            callback: fn ($meta) => [
                // Add the property name to the schema array.
                'name' => $meta->name,

                // Add the property type to the schema array. If no type is defined, default to 'mixed'.
                'type' => $meta->property->getType()?->getName() ?? 'mixed',

                // Add the nullability information of the property to the schema array.
                'nullable' => $meta->isNullable(),

                // Map the attributes of the property to their names and add them to the schema array.
                'attributes' => array_map(callback: fn ($a) => $a->getName(), array: $meta->attributes),
            ],

            // Retrieve metadata for all public fields of the target object.
            // Metadata includes details about the properties of the object being reflected.
            array   : $this->reflectPublicFields()
        );
    }
}
