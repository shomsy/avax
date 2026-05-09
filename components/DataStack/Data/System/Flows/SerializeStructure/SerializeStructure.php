<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\SerializeStructure;

use JsonException;

/**
 * SerializeStructure — converts data structures to stable array/JSON representations.
 *
 * Follows the AvaX serialization convention:
 * - toArray returns a lossless representation where meaningful
 * - toJson returns a JSON string
 * - Structures that cannot be meaningfully serialized throw
 */
final class SerializeStructure
{
    /**
     * Serialize a value to JSON.
     *
     * @throws JsonException
     */
    public function toJson(mixed $value, int $flags = JSON_THROW_ON_ERROR) : string
    {
        $result = json_encode(value: $this->toArray(value: $value), flags: $flags);
        if ($result === false) {
            throw new JsonException(message: 'Failed to serialize value to JSON.');
        }

        return $result;
    }

    /**
     * Serialize a value to array.
     *
     * Handles: scalar, array, object with toArray(), object with __serialize().
     *
     * @return array<string, mixed>|list<mixed>|mixed
     */
    public function toArray(mixed $value) : mixed
    {
        return match (true) {
            is_scalar($value), $value === null   => $value,
            is_array($value)                     => $value,
            method_exists($value, 'toArray')     => $value->toArray(),
            method_exists($value, '__serialize') => $value->__serialize(),
            default                              => (array) $value,
        };
    }

    /**
     * Check whether a value supports meaningful serialization.
     */
    public function isSerializable(mixed $value) : bool
    {
        return is_scalar($value)
            || $value === null
            || is_array($value)
            || method_exists($value, 'toArray')
            || method_exists($value, '__serialize');
    }
}
