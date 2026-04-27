<?php

declare(strict_types=1);

namespace Avax\DataFoundation\ObjectHandling\DTO\Support;

use Avax\DataFoundation\ObjectHandling\DTO\DTOValidationException;
use Avax\DataFoundation\ObjectHandling\DTO\Traits\CastsTypes;
use Avax\DataFoundation\ObjectHandling\DTO\Traits\HandlesAttributes;
use Avax\DataFoundation\ObjectHandling\DTO\Traits\InspectsProperties;
use Avax\DataFoundation\ObjectHandling\DTO\Traits\Serialization;
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
 *
 * @final This class is immutable in its implementation and should not be extended.
 */
final class Reflector
{
    /**
     * Use traits that modularize reflection-based behaviors.
     * - `InspectsProperties`: Adds the ability to inspect DTO object's properties.
     * - `CastsTypes`: Handles casting raw values to expected types as part of hydration.
     * - `HandlesAttributes`: Processes and applies custom attribute-based rules on properties.
     * - `Serialization`: Offers serialization support for the DTO.
     */
    use InspectsProperties;
    use CastsTypes;
    use HandlesAttributes;
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
     * @param object $target The target object for reflection and operations.
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
     * @param object $instance The object instance being wrapped by the reflector.
     *
     * @return self Returns a new Reflector instance.
     */
    public static function fromInstance(object $instance) : self
    {
        return new self(target: $instance);
    }

    /**
     * Creates a Reflector instance for a given class name.
     *
     * Uses `ReflectionClass` to instantiate the object without calling its constructor,
     * allowing flexibility for reflection-based object construction and hydration.
     *
     * @param string $className The fully qualified class name of the target object.
     *
     * @return self Returns a new Reflector instance wrapping the created object.
     * @throws ReflectionException If the provided class does not exist or cannot be instantiated.
     */
    public static function fromClass(string $className) : self
    {
        return new self(target: new ReflectionClass(objectOrClass: $className)->newInstanceWithoutConstructor());
    }

    /**
     * Hydrates the target object with the provided raw data.
     *
     * Iterates over the public properties of the target object and applies the
     * given raw data to each property. Attributes and type safety rules are
     * respected during the process, ensuring that all DTO constraints are enforced.
     *
     * @param array<string, mixed> $data An associative array mapping property names
     *                                   to their corresponding values.
     *
     * @throws DTOValidationException If hydration fails due to validation or type casting errors.
     * @throws ReflectionException    If reflection operations encounter an issue.
     */
    public function hydrate(array $data) : void
    {
        $errors = [];

        foreach ($this->reflectPublicFields() as $meta) {
            try {
                $this->hydrateField(
                    name      : $meta->name,
                    property  : $meta->property,
                    attributes: $meta->attributes,
                    data      : $data
                );
            } catch (Throwable $exception) {
                $errors[$meta->name] = $this->formatHydrationError(
                    fieldName: $meta->name,
                    exception: $exception
                );
            }
        }

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
     * @param string             $name       The name of the property being hydrated.
     * @param ReflectionProperty $property   The reflection of the target property.
     * @param array              $attributes An array of attributes applied to the property.
     * @param array              $data       The raw input data used for hydration.
     */
    private function hydrateField(
        string             $name,
        ReflectionProperty $property,
        array              $attributes,
        array              $data
    ) : void
    {
        if (! array_key_exists($name, $data)) {
            $this->handleMissingField(name: $name, property: $property);

            return;
        }

        $rawValue = $data[$name];

        $resolvedValue = $this->castToExpectedType(property: $property, value: $rawValue);

        $resolvedValue = $this->applyFieldAttributes(
            fieldName : $name,
            value     : $resolvedValue,
            attributes: $attributes
        );

        $this->target->{$name} = $resolvedValue;
    }

    /**
     * Handles cases where required data for a field is missing.
     *
     * @param string             $name     The name of the missing property.
     * @param ReflectionProperty $property The reflection of the target property.
     *
     * @throws InvalidArgumentException If no suitable value is found for the missing property.
     */
    private function handleMissingField(string $name, ReflectionProperty $property) : void
    {
        if ($this->isPropertyNullable(property: $property)) {
            $this->target->{$name} = null;

            return;
        }

        if ($property->hasDefaultValue()) {
            $this->target->{$name} = $property->getDefaultValue();

            return;
        }

        throw new InvalidArgumentException(message: "Missing required field: {$name}");
    }

    /**
     * Formats detailed error messages for failed hydration of a single field.
     *
     * @param string    $fieldName The name of the field where hydration failed.
     * @param Throwable $exception The exception that occurred during hydration.
     *
     * @return string Returns a string describing the error with the field's name and exception message.
     */
    private function formatHydrationError(string $fieldName, Throwable $exception) : string
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
    public function getTarget() : object
    {
        return $this->target;
    }

    /**
     * Converts the public properties of a target object into a schema-friendly array format.
     *
     * @return array An array representing the schema of the object's public fields.
     * @throws ReflectionException If reflection operations encounter an error.
     */
    public function toSchema() : array
    {
        return array_map(
            fn ($meta) => [
                'name'       => $meta->name,
                'type'       => $meta->property->getType()?->getName() ?? 'mixed',
                'nullable'   => $meta->isNullable(),
                'attributes' => array_map(fn ($a) => $a->getName(), $meta->attributes),
            ],
            $this->reflectPublicFields()
        );
    }
}
