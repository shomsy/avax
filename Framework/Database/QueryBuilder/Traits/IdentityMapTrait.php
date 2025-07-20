<?php

declare(strict_types=1);

namespace Gemini\Database\QueryBuilder\Traits;

use Gemini\Database\QueryBuilder\Exception\QueryBuilderException;

/**
 * **IdentityMapTrait**
 *
 * Implements the **Identity Map** pattern to cache and retrieve objects
 * within a single database transaction or request lifecycle.
 *
 * 🏆 **Benefits:**
 * - ✅ **Prevents redundant queries** by storing retrieved data in memory.
 * - ✅ **Ensures consistency** by always returning the same instance of an entity.
 * - ✅ **Improves performance** by reducing database hits.
 * - ✅ **Supports cache integrations** (Redis, APCu, etc.).
 */
trait IdentityMapTrait
{
    /**
     * Stores cached entities, indexed by their unique keys.
     *
     * @var array<string,|null mixed>
     */
    private readonly array|null $identityMap;

    /**
     * Adds an entity to the identity map.
     *
     * If an entity with the same key already exists, it will be **overwritten**.
     *
     * @param string $key   The unique identifier for the entity.
     * @param mixed  $value The entity data to be stored.
     */
    public function addToIdentityMap(string $key, mixed $value) : void
    {
        $this->identityMap[$key] = $value;
    }

    /**
     * Retrieves an entity from the identity map by its key.
     *
     * @param string $key The unique identifier of the entity.
     *
     * @return mixed|null The stored entity if found, otherwise `null`.
     */
    public function getFromIdentityMap(string $key) : mixed
    {
        return $this->identityMap[$key] ?? null;
    }

    /**
     * Checks if an entity exists in the identity map.
     *
     * @param string $key The unique identifier of the entity.
     *
     * @return bool Returns `true` if the entity exists, otherwise `false`.
     */
    public function hasInIdentityMap(string $key) : bool
    {
        return array_key_exists($key, $this->identityMap);
    }

    /**
     * Removes an entity from the identity map.
     *
     * @param string $key The unique identifier of the entity.
     *
     * @throws \Gemini\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \Gemini\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function removeFromIdentityMap(string $key) : void
    {
        if (! array_key_exists($key, $this->identityMap)) {
            throw new QueryBuilderException(message: "Cannot remove entity: Key '{$key}' not found in Identity Map.");
        }

        unset($this->identityMap[$key]);
    }

    /**
     * Clears all stored entities from the identity map.
     */
    public function clearIdentityMap() : void
    {
        $this->identityMap = [];
    }
}
