<?php

declare(strict_types=1);

namespace Avax\DataHandling\ObjectHandling\DTO\Traits;

use BackedEnum;
use DateTimeInterface;
use Avax\DataHandling\ObjectHandling\Collections\Collection;
use Avax\DataHandling\Validation\Attributes\Hidden;
use JsonException;
use JsonSerializable;
use ReflectionException;
use ReflectionProperty;
use stdClass;
use Traversable;

/**
 * Trait Serialization
 *
 * Provides recursive, flexible serialization capabilities for Data Transfer Objects (DTO).
 * Includes support for:
 * - JSON serialization with optional formatting.
 * - Recursive normalization of nested data structures.
 * - Handling of custom types such as enums, dates, and objects.
 * - Filtering of fields using #[Hidden] attributes.
 *
 * Designed to integrate seamlessly with Domain-Driven Design (DDD) practices.
 */
trait Serialization
{
    /**
     * Converts the DTO to a JSON string representation.
     * Useful for logging or debugging purposes.
     *
     * @return string The JSON representation of the DTO.
     * @throws JsonException|ReflectionException If an error occurs during encoding.
     */
    public function __toString() : string
    {
        return $this->toJson(flags: JSON_PRETTY_PRINT); // Beautify JSON output for readability.
    }

    /**
     * Encodes the DTO to a JSON string with optional flags and encoding depth.
     *
     * @param int|null $flags Optional JSON encoding flags (e.g., JSON_PRETTY_PRINT).
     * @param int      $depth Maximum depth for JSON serialization to prevent infinite recursion.
     *
     * @return string The JSON-encoded string representation of the DTO.
     * @throws JsonException|ReflectionException If JSON encoding fails.
     */
    public function toJson(int|null $flags = null, int $depth = 512) : string
    {
        $flags ??= 0; // Default to no flags if none provided.

        return json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR, $depth); // Encode object as JSON.
    }

    /**
     * Converts the DTO into an associative array representation.
     * Recurses through nested properties and filters hidden fields if configured.
     *
     * @param int|null $depth         Maximum recursion depth, null for unlimited depth.
     * @param bool     $excludeHidden Whether to exclude fields marked with #[Hidden] attribute.
     *
     * @return array<string, mixed> The DTO as an associative array.
     * @throws ReflectionException
     */
    public function toArray(int|null $depth = null, bool $excludeHidden = true) : array
    {
        // Normalize and filter object properties depending on the excludeHidden flag.
        return $this->normalizeValue(
            value: $excludeHidden
                       ? $this->filterHiddenFields(get_object_vars($this)) // Filter hidden fields.
                       : get_object_vars($this),
            depth: $depth
        );
    }

    /**
     * Recursively normalizes a given value into a JSON-safe structure.
     * Supports enums, date objects, JSON-serializable objects, arrays, and iterables.
     *
     * @param mixed    $value Any value to normalize.
     * @param int|null $depth Maximum depth for recursion, null for unlimited depth.
     *
     * @return mixed The normalized, JSON-serializable value.
     * @throws ReflectionException
     */
    protected function normalizeValue(mixed $value, int|null $depth = null) : mixed
    {
        // Return early if recursion depth has reached zero.
        if ($depth === 0) {
            return null; // Prevent infinite recursion.
        }

        // Match on specific value types and normalize accordingly.
        return match (true) {
            $value instanceof self              => $value->toArray(
                depth: $depth !== null ? $depth - 1 : null
            ), // Normalize nested DTO objects recursively.
            $value instanceof BackedEnum        => $value->value, // Return enum value.
            $value instanceof DateTimeInterface => $value->format(
                format: DATE_ATOM
            ), // Format dates as ISO 8601 strings.
            $value instanceof JsonSerializable  => $value->jsonSerialize(), // Serialize JSON-serializable objects.
            $value instanceof Traversable       => array_map(
            // Convert iterable objects to arrays and normalize their items.
                fn($item) => $this->normalizeValue(value: $item, depth: $depth !== null ? $depth - 1 : null),
                iterator_to_array($value)
            ),
            is_array($value)                    => array_map(
            // Normalize and recurse through array elements.
                fn($item) => $this->normalizeValue(value: $item, depth: $depth !== null ? $depth - 1 : null),
                $value
            ),
            is_object($value) && method_exists(
                $value,
                '__toString'
            )                                   => (string) $value, // Convert objects with __toString to strings.
            is_object(
                $value
            )                                   => (array) $value, // Fallback: convert objects to arrays.
            default                             => $value, // Default case: return the value as-is.
        };
    }

    /**
     * Implements the JsonSerializable interface by converting the DTO to an array.
     *
     * @return array A JSON-serializable representation of the DTO.
     * @throws ReflectionException
     */
    public function jsonSerialize() : array
    {
        return $this->toArray(); // Re-use the toArray method for serialization.
    }

    /**
     * Filters out fields marked with the #[Hidden] attribute from an array of properties.
     *
     * @param array<string, mixed> $properties The properties to be filtered.
     *
     * @return array<string, mixed> A filtered associative array of properties.
     * @throws ReflectionException If reflection fails while accessing class properties.
     */
    protected function filterHiddenFields(array $properties) : array
    {
        foreach ($this->reflectPublicFields() as $meta) {
            if ($this->shouldHideField(property: $meta->property)) {
                unset($properties[$meta->name]); // Remove fields marked as hidden.
            }
        }

        return $properties;
    }

    /**
     * Determines whether a given property should be hidden based on the #[Hidden] attribute.
     * Can be extended to provide more sophisticated filtering logic.
     *
     * @param ReflectionProperty $property The property to evaluate.
     *
     * @return bool True if the property should be hidden, false otherwise.
     */
    protected function shouldHideField(ReflectionProperty $property) : bool
    {
        // Check whether the property has the #[Hidden] attribute.
        return $this->hasAttribute(property: $property, attributeFqn: Hidden::class);
    }

    /**
     * Converts the DTO into a flat array with all its properties.
     * Does not normalize or filter hidden fields.
     *
     * @return array<string, mixed> A flat array of the DTO properties.
     */
    public function toFlatArray() : array
    {
        return get_object_vars($this); // Return an associative array of all object properties.
    }

    /**
     * Converts the DTO into an instance of stdClass for compatibility with generic object types.
     *
     * @return stdClass The DTO represented as a standard class object.
     * @throws JsonException If the DTO cannot be encoded into JSON.
     * @throws \ReflectionException
     */
    public function toStdClass() : stdClass
    {
        return json_decode(
            json       : $this->toJson(), // Serialize DTO as JSON.
            associative: false, // Decode JSON as an object, not an array.
            depth      : 512, // Maximum decoding depth.
            flags      : JSON_THROW_ON_ERROR // Throw exceptions on JSON decoding errors.
        );
    }

    /**
     * Transforms the current object into a Collection instance.
     *
     * This method provides a convenient way to convert the object's array representation
     * into a Collection, enabling fluent collection operations on the object's data.
     * The resulting Collection inherits all the powerful collection manipulation methods
     * and can be further chained with other collection operations.
     *
     * @return Collection Immutable collection containing the object's data
     *
     * @throws \ReflectionException When reflection fails to analyze the object structure
     * @api
     * @since 1.0.0
     * @final
     */
    public function toCollection() : Collection
    {
        // Convert the object to an array and wrap it in a Collection instance
        return collect(items: $this->toArray());
    }

    /**
     * Transforms the Data Transfer Object into a JSON:API compliant format.
     *
     * This method implements the JSON:API specification (jsonapi.org) structure,
     * providing a standardized response format with type, id, and attributes.
     *
     * @param string $type The resource type identifier for the JSON:API document
     *
     * @return array<string, array<string, mixed>> The JSON:API formatted response
     * @throws \ReflectionException When reflection fails during property inspection
     *
     * @see https://jsonapi.org/format/ JSON:API Specification
     */
    public function toJsonApi(string $type) : array
    {
        // Create the outer JSON:API compliant structure
        return [
            // Root-level data container as per JSON:API spec
            'data' => [
                // Resource type identifier for the object
                'type'       => $type,
                // Unique identifier for the resource, null if not set
                'id'         => $this->id ?? null,
                // Object attributes normalized through toArray method
                'attributes' => $this->toArray(),
            ],
        ];
    }
}