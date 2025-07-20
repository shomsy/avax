<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Runner\Manifest;

use SleekDB\Store;

/**
 * Concrete implementation of ManifestDBInterface using SleekDB.
 *
 * @final
 */
final class ManifestDB implements ManifestDBInterface
{
    /**
     * The underlying SleekDB store instance.
     *
     * @var Store
     */
    private Store $store;

    /**
     * ManifestDB constructor.
     *
     * @param string $storagePath Path where a manifest database is located.
     *
     * @throws \SleekDB\Exceptions\InvalidConfigurationException
     * @throws \SleekDB\Exceptions\InvalidArgumentException
     * @throws \SleekDB\Exceptions\IOException
     */
    public function __construct(string $storagePath)
    {
        $this->store = new Store(
            storeName    : 'migrations',
            databasePath : $storagePath,
            configuration: ['timeout' => false]
        );
    }


    /**
     * Insert a new manifest record.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     * @throws \SleekDB\Exceptions\IOException
     * @throws \SleekDB\Exceptions\IdNotAllowedException
     * @throws \SleekDB\Exceptions\InvalidArgumentException
     * @throws \SleekDB\Exceptions\JsonException
     */
    public function insert(array $data) : array
    {
        return $this->store->insert($data);
    }

    /**
     * Find manifest records matching given conditions.
     *
     * @param array<int, array<string, mixed>> $conditions
     *
     * @return array<int, array<string, mixed>>
     * @throws \SleekDB\Exceptions\IOException
     * @throws \SleekDB\Exceptions\InvalidArgumentException
     */
    public function find(array $conditions) : array
    {
        return $this->store->findBy($conditions);
    }

    /**
     * Retrieve all manifest records.
     *
     * @return array<int, array<string, mixed>>
     * @throws \SleekDB\Exceptions\IOException
     * @throws \SleekDB\Exceptions\InvalidArgumentException
     */
    public function findAll() : array
    {
        return $this->store->findAll();
    }

    /**
     * Update manifest records matching conditions.
     *
     * @param array<int, array<string, mixed>> $conditions
     * @param array<string, mixed>             $newData
     *
     * @return void
     * @throws \SleekDB\Exceptions\IOException
     * @throws \SleekDB\Exceptions\InvalidArgumentException
     */
    public function update(array $conditions, array $newData) : void
    {
        $this->store
            ->createQueryBuilder()
            ->where($conditions)
            ->getQuery()
            ->update($newData);
    }
}
