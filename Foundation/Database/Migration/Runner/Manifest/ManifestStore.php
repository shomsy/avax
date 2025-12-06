<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Manifest;

use DateTimeImmutable;
use Avax\Database\Migration\Runner\Manifest\DTO\CreateManifestEntryDTO;

/**
 * Service that manages manifest operations.
 */
final readonly class ManifestStore implements ManifestStoreInterface
{
    public function __construct(private ManifestDBInterface $db) {}

    /**
     * @throws \SleekDB\Exceptions\IOException
     * @throws \SleekDB\Exceptions\JsonException
     * @throws \SleekDB\Exceptions\IdNotAllowedException
     * @throws \SleekDB\Exceptions\InvalidArgumentException
     * @throws \ReflectionException
     */
    public function createEntry(CreateManifestEntryDTO $dto) : void
    {
        $this->db->insert($dto->toArray());
    }

    /**
     * @throws \SleekDB\Exceptions\IOException
     * @throws \SleekDB\Exceptions\InvalidArgumentException
     */
    public function fetchAll() : array
    {
        return $this->db->findAll();
    }

    /**
     * @throws \SleekDB\Exceptions\InvalidArgumentException
     * @throws \SleekDB\Exceptions\IOException
     */
    public function findPending() : array
    {
        return $this->db->find(
            [
                ['status', '=', 'pending'],
            ]
        );
    }

    public function rollbackBatch(string $batch) : void
    {
        $this->db->update(
            [['batch', '=', $batch]],
            ['status' => 'rolled_back', 'rolled_back_at' => (new DateTimeImmutable())->format(DATE_ATOM)]
        );
    }

    /**
     * @throws \SleekDB\Exceptions\InvalidArgumentException
     * @throws \SleekDB\Exceptions\IOException
     */
    public function findByMigrationName(string $migrationName) : array|null
    {
        $found = $this->db->find(
            [
                ['migration', '=', $migrationName],
            ]
        );

        return $found[0] ?? null;
    }
}