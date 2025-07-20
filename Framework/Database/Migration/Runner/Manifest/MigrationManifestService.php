<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Runner\Manifest;

use SleekDB\Store;

/**
 * Service for managing the Migration Manifest entries.
 *
 * Handles creation, retrieval, updating, and validation of migration metadata.
 *
 * @package Gemini\Database\Migration\Runner\Manifest
 *
 * @final   This class is immutable and must not be extended.
 */
final class MigrationManifestService
{
    /**
     * SleekDB Store instance for Manifest storage.
     *
     * @var Store
     */
    private Store $store;

    /**
     * Constructor.
     *
     * Initializes the SleekDB Store directly.
     *
     * @param string $manifestPath Absolute path to the manifest storage directory.
     *
     * @throws \SleekDB\Exceptions\IOException
     * @throws \SleekDB\Exceptions\InvalidArgumentException
     * @throws \SleekDB\Exceptions\InvalidConfigurationException
     */
    public function __construct(string $manifestPath)
    {
        $this->store = new Store(
            storeName    : 'manifest',
            databasePath : $manifestPath,
            configuration: ['auto_cache' => true]
        );
    }

    /**
     * Creates a new Manifest entry.
     *
     * @param MigrationManifestEntry $entry Data Transfer Object representing the migration manifest.
     *
     * @return void
     * @throws \SleekDB\Exceptions\IOException
     * @throws \SleekDB\Exceptions\IdNotAllowedException
     * @throws \SleekDB\Exceptions\InvalidArgumentException
     * @throws \SleekDB\Exceptions\JsonException
     */
    public function createEntry(MigrationManifestEntry $entry) : void
    {
        $this->store->insert(
            [
                'migration'      => $entry->migrationName,
                'file'           => $entry->fileName,
                'status'         => $entry->status,
                'hash'           => $entry->hash,
                'batch'          => $entry->batch,
                'executed_at'    => $entry->executedAt,
                'rolled_back_at' => $entry->rolledBackAt,
                'tenant_id'      => $entry->tenantId,
                'tags'           => $entry->tags,
                'logs'           => $entry->logs,
                'created_at'     => $entry->createdAt->format(DATE_ATOM),
            ]
        );
    }

    /**
     * Retrieves all Manifest entries.
     *
     * @return array<int, array<string, mixed>> List of all migration manifest entries.
     */
    public function all() : array
    {
        return $this->store->fetch();
    }

    /**
     * Finds a specific migration entry by its migration name.
     *
     * @param string $migrationName Logical name of the migration.
     *
     * @return array<string, mixed>|null The matching manifest entry or null if not found.
     */
    public function find(string $migrationName) : array|null
    {
        $result = $this->store
            ->where('migration', '=', $migrationName)
            ->fetch();

        return $result[0] ?? null;
    }
}
