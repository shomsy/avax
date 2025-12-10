<?php

declare(strict_types=1);

namespace Shomsy\Components\Foundation\HTTP\Session\Data;

/**
 * Session Component
 *
 * Migrator - Session Data Migrator
 *
 * Migrates session data from one storage backend to another.
 * Useful for transitioning between different session storage implementations.
 *
 * Use cases:
 * - Moving from file-based to Redis storage
 * - Transitioning to database sessions
 * - Data backup and restoration
 *
 * @author  Milos Stankovic
 * @package Shomsy\Components\Foundation\HTTP\Session\Data
 */
final class Migrator
{
    /**
     * Migrator Constructor.
     *
     * @param StoreInterface $source Source storage backend.
     * @param StoreInterface $target Target storage backend.
     */
    public function __construct(
        private StoreInterface $source,
        private StoreInterface $target
    ) {}

    /**
     * Migrate specific keys only.
     *
     * @param array<string> $keys Keys to migrate.
     *
     * @return int Number of migrated items.
     */
    public function migrateKeys(array $keys) : int
    {
        $count = 0;
        foreach ($keys as $key) {
            if ($this->source->has($key)) {
                $value = $this->source->get($key);
                $this->target->put($key, $value);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Migrate and clear source.
     *
     * @return int Number of migrated items.
     */
    public function migrateAndClear() : int
    {
        $count = $this->migrate();
        $this->source->flush();

        return $count;
    }

    /**
     * Migrate all data from source to target.
     *
     * @return int Number of migrated items.
     */
    public function migrate() : int
    {
        $count = 0;
        foreach ($this->source->all() as $key => $value) {
            $this->target->put($key, $value);
            $count++;
        }

        return $count;
    }

    /**
     * Verify migration integrity.
     *
     * Checks if all data was successfully migrated.
     *
     * @return bool True if all data matches.
     */
    public function verify() : bool
    {
        $sourceData = $this->source->all();
        $targetData = $this->target->all();

        if (count($sourceData) !== count($targetData)) {
            return false;
        }

        foreach ($sourceData as $key => $value) {
            if (! isset($targetData[$key]) || $targetData[$key] !== $value) {
                return false;
            }
        }

        return true;
    }
}
