<?php

declare(strict_types=1);

namespace Avax\DataHandling\ObjectHandling\DTO\Traits;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use BackedEnum;
use InvalidArgumentException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

/**
 * Trait CastsTypes
 *
 * This trait provides sophisticated value-casting mechanisms
 * to enable flexible and reliable data transfer object (DTO) hydration.
 *
 * Supports:
 * - Nested DTO instances
 * - Arrays of DTOs (DTO[])
 * - Backed Enums
 * - Primitive type fallback pass through
 *
 * Uses strict type checking and powerful casting techniques
 * to resolve property type constraints dynamically.
 */
trait CastsTypes
{
    /**
     * Provides a public entry point to the internal casting mechanism.
     *
     * @param ReflectionProperty $property The property to cast the value for.
     * @param mixed              $value    The raw value to be casted.
     *
     * @return mixed The casted value matching the expected type of the property.
     */
    public function castTo(ReflectionProperty $property, mixed $value) : mixed
    {
        // Delegates to the internal casting method.
        return $this->castToExpectedType(property: $property, value: $value);
    }

    /**
     * Dynamically dispatches value casting logic based on the property's type metadata.
     *
     * Uses `match` to select the appropriate casting method:
     * - DTO detection
     * - Array of DTOs detection
     * - Backed Enums detection
     *
     * Falls back to the raw value if no special handling is needed.
     *
     * @param ReflectionProperty $property The property to cast the value for.
     * @param mixed              $value    The raw value to be casted.
     *
     * @return mixed The casted value (or the original value if no special casting is applied).
     */
    protected function castToExpectedType(ReflectionProperty $property, mixed $value) : mixed
    {
        return match (true) {
            $this->isDTOType(property: $property)    => $this->castToDTO(property: $property, value: $value),
            $this->isDTOArray(property: $property)   => $this->castToDTOArray(property: $property, value: $value),
            $this->isBackedEnum(property: $property) => $this->castToEnum(property: $property, value: $value),
            default                                  => $value,
        };
    }

    /**
     * Checks if the given property is a subclass of the current DTO base class.
     *
     * @param ReflectionProperty $property The property to inspect.
     *
     * @return bool `true` if the property maps to a DTO class, `false` otherwise.
     */
    protected function isDTOType(ReflectionProperty $property) : bool
    {
        $type = $this->resolvePropertyType(property: $property);

        return $type !== null && is_subclass_of(object_or_class: $type, class: AbstractDTO::class);
    }

    /**
     * Resolves the fully qualified class name or built-in type of a property.
     *
     * Prioritizes class names over scalars when multiple union types are present.
     *
     * @param ReflectionProperty $property The property for which to determine the type.
     *
     * @return string|null The resolved class or scalar type name, or null if unavailable.
     */
    protected function resolvePropertyType(ReflectionProperty $property) : string|null
    {
        $type = $property->getType();

        // If no type is declared, return null
        if ($type === null) {
            return null;
        }

        // Handle single-named types directly
        if ($type instanceof ReflectionNamedType) {
            return $type->getName();
        }

        // Handle union types (e.g., string|int|EnumType)
        if ($type instanceof ReflectionUnionType) {
            // Extract only named types excluding null/mixed/etc.
            $types = array_filter(
                array   : $type->getTypes(),
                callback: fn($t) => $t instanceof ReflectionNamedType && $t->getName() !== 'null'
            );

            // Prioritize classes (DTO/Enum) over scalar primitives
            usort(
                array   : $types,
                callback: fn(ReflectionNamedType $a, ReflectionNamedType $b) : int => class_exists(
                        class: $b->getName()
                    ) <=> class_exists(class: $a->getName())
            );

            return $types[0]?->getName();
        }

        // Handle intersection types (PHP 8.2+)
        if ($type instanceof ReflectionIntersectionType) {
            foreach ($type->getTypes() as $named) {
                if ($named instanceof ReflectionNamedType) {
                    return $named->getName();
                }
            }
        }

        return null;
    }

    /**
     * Casts a given value to a DTO instance.
     *
     * Initializes a new DTO instance by passing a normalized array of values to its constructor.
     *
     * @param ReflectionProperty $property The property to cast the value for.
     * @param mixed              $value    The raw value to be casted.
     *
     * @return object A new DTO instance based on the resolved class type.
     *
     * @throws InvalidArgumentException If the resolved class is invalid or not a DTO.
     */
    protected function castToDTO(ReflectionProperty $property, mixed $value) : object
    {
        $class = $this->resolvePropertyType(property: $property);
        $this->assertDTOClass(class: $class, property: $property);

        // Instantiate the DTO using the normalized array of input data.
        return new $class($this->normalizeToArray(value: $value));
    }

    /**
     * Validates whether the given class is a valid subclass of the DTO base class.
     *
     * @param string|null        $class    The class name to validate.
     * @param ReflectionProperty $property The property for which the class is being validated.
     *
     * @throws InvalidArgumentException If the class is not a valid DTO.
     */
    protected function assertDTOClass(string|null $class, ReflectionProperty $property) : void
    {
        if ($class === null || ! class_exists(class: $class) || ! is_subclass_of(object_or_class: $class, class: AbstractDTO::class)) {
            throw new InvalidArgumentException(
                message: sprintf(
                    "Invalid DTO class '%s' for property '%s'.",
                    $class ?? 'null',
                    $property->getName()
                )
            );
        }
    }

    /**
     * Normalizes a mixed input value into an array.
     *
     * Ensures that values can be safely passed as an array during DTO instantiation.
     *
     * @param mixed $value The raw input value.
     *
     * @return array The normalized array representation of the input.
     */
    protected function normalizeToArray(mixed $value) : array
    {
        return is_array(value: $value) ? $value : (array) $value;
    }

    /**
     * Checks if the given property corresponds to an array of DTO instances.
     *
     * Determines this by analyzing the type information and optional metadata
     * from PHPDoc annotations or attributes.
     *
     * @param ReflectionProperty $property The property to inspect.
     *
     * @return bool `true` if the property is an array of DTOs, `false` otherwise.
     */
    protected function isDTOArray(ReflectionProperty $property) : bool
    {
        return $this->resolvePropertyType(property: $property) === 'array'
            && $this->resolveDTOClassFromAnnotationsOrAttributes(property: $property) !== null;
    }

    /**
     * Resolves the class name of the DTO from either PHP attributes or @param ReflectionProperty $property The
     * property for which to resolve the class.
     *
     * @return string|null The fully qualified class name of the DTO, or `null` if not found.
     * "@var annotations"
     *
     */
    protected function resolveDTOClassFromAnnotationsOrAttributes(ReflectionProperty $property) : string|null
    {
        // Check for attributes first.
        foreach ($property->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();
            if (method_exists(object_or_class: $instance, method: 'of')) {
                return $instance->of();
            }
        }

        // Fallback to PHPDoc annotations.
        $doc = $property->getDocComment();
        if ($doc && preg_match(pattern: '/@var\s+([\w\\\\]+)\[\]/', subject: $doc, matches: $matches)) {
            return ltrim(string: $matches[1], characters: '\\');
        }

        return null;
    }

    /**
     * Casts a given value to an array of DTO instances.
     *
     * Iterates over the input array and creates a new DTO instance for each element.
     *
     * @param ReflectionProperty $property The property to cast the value for.
     * @param mixed              $value    The raw value (array) to be casted.
     *
     * @return array An array of DTO instances.
     *
     * @throws InvalidArgumentException If the DTO class is invalid.
     */
    protected function castToDTOArray(ReflectionProperty $property, mixed $value) : array
    {
        $class = $this->resolveDTOClassFromAnnotationsOrAttributes(property: $property);
        $this->assertDTOClass(class: $class, property: $property);

        // Map each array element to a new DTO instance.
        return array_map(
            callback: fn($item) => new $class($this->normalizeToArray(value: $item)),
            array   : is_array(value: $value) ? $value : []
        );
    }

    /**
     * Checks if the given property maps to a backed enum.
     *
     * @param ReflectionProperty $property The property to inspect.
     *
     * @return bool `true` if the property type is a subclass of `BackedEnum`, `false` otherwise.
     */
    protected function isBackedEnum(ReflectionProperty $property) : bool
    {
        $type = $this->resolvePropertyType(property: $property);

        return $type !== null
            && enum_exists(enum: $type)
            && is_subclass_of(object_or_class: $type, class: BackedEnum::class);
    }

    /**
     * Casts a scalar value to its corresponding backed enum instance.
     *
     * @param ReflectionProperty $property The property to cast the value for.
     * @param mixed              $value    The raw scalar value to be converted.
     *
     * @return BackedEnum|string|null The enum instance corresponding to the given value.
     *
     */
    protected function castToEnum(ReflectionProperty $property, mixed $value) : BackedEnum|string|null
    {
        if ($value === null) {
            return null;
        }

        $type = $this->resolvePropertyType(property: $property);
        $this->assertEnumClass(class: $type, property: $property);

        /** @var class-string<BackedEnum> $type */
        if ($value instanceof $type) {
            return $value;
        }

        $enum = $type::tryFrom(value: $value);

        if (! $enum) {
            throw new InvalidArgumentException(
                message: sprintf(
                    "Invalid enum value '%s' for '%s' on property '%s'. Valid: [%s]",
                    is_scalar(value: $value) ? $value : gettype(value: $value),
                    $type,
                    $property->getName(),
                    implode(separator: ', ', array: array_map(callback: static fn($case) => $case->value, array: $type::cases()))
                )
            );
        }

        return $enum;
    }


    /**
     * Asserts that the given type is a valid backed enum class.
     *
     * @param string|null        $class    The class name to validate.
     * @param ReflectionProperty $property The property for which the enum is being validated.
     *
     * @throws InvalidArgumentException If the class is not a valid backed enum.
     */
    protected function assertEnumClass(string|null $class, ReflectionProperty $property) : void
    {
        if ($class === null || ! enum_exists(enum: $class) || ! is_subclass_of(object_or_class: $class, class: BackedEnum::class)) {
            throw new InvalidArgumentException(
                message: sprintf(
                    "Invalid enum type '%s' for property '%s'. Must be a backed enum.",
                    $class ?? 'null',
                    $property->getName()
                )
            );
        }
    }
}