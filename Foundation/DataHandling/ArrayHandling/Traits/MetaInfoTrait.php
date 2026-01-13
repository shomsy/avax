<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling\Traits;

use Carbon\Carbon;
use Closure;
use Exception;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

/**
 * Trait MetaInfoTrait
 *
 * Provides methods to enrich items within a collection with metadata such as GUIDs, timestamps, and version
 * information. It also offers functionality to clone the collection.
 *
 * This trait enforces the implementation of `getItems()`, `setItems()`, `map()`, and `toArray()` methods
 * in the using class to manage and transform the underlying data collection.
 */
trait MetaInfoTrait
{
    use AbstractDependenciesTrait;
    use TransformationTrait;

    /**
     * Add a unique GUID to each item in the collection.
     *
     * This method enriches each item with a universally unique identifier (UUID) under the 'id' key.
     *
     * @return static A new instance with GUIDs added to each item.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $newArrh = $arrh->guid();
     * // $newArrh contains:
     * // [
     * //     ['id' => 'uuid1', 'data' => 'apple'],
     * //     ['id' => 'uuid2', 'data' => 'banana'],
     * //     ['id' => 'uuid3', 'data' => 'cherry']
     * // ]
     * ```
     */
    public function guid() : static
    {
        return $this->map(callback: static function ($item) : array {
            try {
                return [
                    'id'   => Uuid::uuid4()->toString(),
                    'data' => $item,
                ];
            } catch (Exception $e) {
                throw new InvalidArgumentException(message: 'Failed to generate UUID: ' . $e->getMessage(), code: $e->getCode(), previous: $e);
            }
        });
    }

    /**
     * Enforce the implementation of the map method.
     *
     * Classes using this trait must implement this method.
     *
     * @param Closure $callback The callback to apply to each item.
     *
     * @return static A new instance with the transformed items.
     */
    abstract public function map(Closure $callback) : static;

    /**
     * Set or retrieve timestamps for items in the collection.
     *
     * When setting, this method adds a 'timestamp' key with the current time formatted as specified.
     * When retrieving, it extracts the 'timestamp' from each item.
     *
     * @param bool   $set    True to set the current timestamp, false to retrieve.
     * @param string $format Optional date format for timestamp. Defaults to Unix timestamp ('U').
     *
     * @return static A new instance with timestamps set or retrieved.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $timestamped = $arrh->timestamp();
     * // $timestamped contains:
     * // [
     * //     ['timestamp' => 'current_timestamp', 'data' => 'apple'],
     * //     ['timestamp' => 'current_timestamp', 'data' => 'banana'],
     * //     ['timestamp' => 'current_timestamp', 'data' => 'cherry']
     * // ]
     *
     * $timestamps = $arrh->timestamp(false);
     * // $timestamps contains: ['current_timestamp', 'current_timestamp', 'current_timestamp']
     * ```
     */
    public function timestamp(bool|null $set = null, string $format = 'U') : static
    {
        $set ??= true;
        try {
            $timestamp = Carbon::now()->format(format: $format);
        } catch (Exception $exception) {
            throw new InvalidArgumentException(
                message : 'Invalid date format: ' . $exception->getMessage(),
                code    : $exception->getCode(),
                previous: $exception
            );
        }

        return $this->map(
            callback: static function ($item) use ($set, $timestamp) {
                if ($set) {
                    return [
                        'timestamp' => $timestamp,
                        'data'      => $item,
                    ];
                }

                return $item['timestamp'] ?? null;
            }
        );
    }

    /**
     * Add version information to each item in the collection.
     *
     * This method enriches each item with a 'version' key indicating the version number.
     *
     * @param int $version Version number to assign. Defaults to 1.
     *
     * @return static A new instance with version numbers added to each item.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $versioned = $arrh->version(2);
     * // $versioned contains:
     * // [
     * //     ['version' => 2, 'data' => 'apple'],
     * //     ['version' => 2, 'data' => 'banana'],
     * //     ['version' => 2, 'data' => 'cherry']
     * // ]
     * ```
     */
    public function version(int $version = 1) : static
    {
        return $this->map(callback: static fn($item) : array => [
            'version' => $version,
            'data'    => $item,
        ]);
    }

    /**
     * Create a deep clone of the collection.
     *
     * This method creates a new instance of the collection with a deep copy of the current items.
     *
     * @return static Cloned collection.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $cloned = $arrh->clone();
     * // $cloned is a separate instance with the same items
     * ```
     */
    public function clone() : static
    {
        return new static(items: $this->toArray());
    }

    /**
     * Enforce the implementation of the toArray method.
     *
     * Classes using this trait must implement this method.
     *
     * @return array The collection represented as an array.
     */
    abstract public function toArray() : array;
}
