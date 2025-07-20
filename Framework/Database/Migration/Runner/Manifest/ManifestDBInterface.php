<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Runner\Manifest;

/**
 * Represents the persistence contract for migration manifest entries.
 *
 * This interface defines the repository contract for managing migration manifest records
 * in a persistence store. It follows the Repository Pattern from DDD and ensures
 * a consistent way to handle migration metadata across different storage implementations.
 *
 * @package Gemini\Database\Migration\Runner\Manifest
 */
interface ManifestDBInterface
{
    /**
     * Persists a new migration manifest entry to the storage.
     *
     * This method is responsible for creating a new record in the persistence layer.
     * It encapsulates the storage-specific implementation details while maintaining
     * a consistent interface for manifest entry creation.
     *
     * @param array<string, mixed> $data The manifest entry data to persist
     *
     * @return array<string, mixed> The persisted manifest entry with any storage-generated metadata
     *
     * @throws \SleekDB\Exceptions\IOException When storage operation fails
     * @throws \SleekDB\Exceptions\InvalidArgumentException When provided data is invalid
     * @throws \SleekDB\Exceptions\JsonException When JSON serialization fails
     * @throws \SleekDB\Exceptions\IdNotAllowedException When ID field conflicts occur
     */
    public function insert(array $data) : array;

    /**
     * Retrieves manifest entries matching specified criteria.
     *
     * Implements specification pattern for flexible querying of manifest entries.
     * Supports complex query conditions while abstracting storage-specific query syntax.
     *
     * @param array<int, array<string|array>> $conditions Query specifications for filtering entries
     *
     * @return array<int, array<string, mixed>> Collection of manifest entries matching the conditions
     *
     * @throws \SleekDB\Exceptions\IOException When storage read operation fails
     * @throws \SleekDB\Exceptions\InvalidArgumentException When query conditions are invalid
     */
    public function find(array $conditions) : array;

    /**
     * Retrieves all manifest entries from the storage.
     *
     * Provides a way to access the complete migration history. Use with caution
     * in large datasets as it may impact performance.
     *
     * @return array<int, array<string, mixed>> Complete collection of manifest entries
     *
     * @throws \SleekDB\Exceptions\IOException When storage read operation fails
     * @throws \SleekDB\Exceptions\InvalidArgumentException When internal query fails
     */
    public function findAll() : array;

    /**
     * Updates existing manifest entries matching the specified criteria.
     *
     * Supports atomic updates of manifest entries based on matching conditions.
     * Implements bulk update capability for efficient batch processing.
     *
     * @param array<int, array<string|array>> $conditions Specifications for identifying entries to update
     * @param array<string, mixed>            $newData    Updated data to apply to matching entries
     *
     * @throws \SleekDB\Exceptions\IOException When storage operation fails
     * @throws \SleekDB\Exceptions\InvalidArgumentException When conditions or data are invalid
     */
    public function update(array $conditions, array $newData) : void;
}