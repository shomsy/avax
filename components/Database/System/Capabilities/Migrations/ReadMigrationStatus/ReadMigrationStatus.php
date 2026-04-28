<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Migrations\ReadMigrationStatus;

use Avax\Components\Database\System\Capabilities\Migrations\LoadMigrations\MigrationLoader;
use Avax\Components\Database\System\Capabilities\Migrations\RunMigrations\MigrationRepository;
use Throwable;

/**
 * Reads current migration status, pending state, and checksum integrity.
 */
final readonly class ReadMigrationStatus
{
    public function __construct(
        private MigrationRepository $repository,
        private MigrationLoader     $loader
    ) {}

    /**
     * @return array{
     *   rows: array<int, array{migration: string, status: string, integrity: string}>,
     *   summary: array{total: int, ran: int, pending: int}
     * }
     *
     * @throws Throwable
     */
    public function read(string $path) : array
    {
        $all    = $this->loader->load(path: $path);
        $ran    = $this->repository->getRan();
        $ranMap = array_column(array: $ran, column_key: 'checksum', index_key: 'migration');
        $rows   = [];

        foreach ($all as $name => $migration) {
            $isRan     = isset($ranMap[$name]);
            $integrity = '---';

            if ($isRan) {
                $dbChecksum   = $ranMap[$name];
                $fileChecksum = $this->loader->getChecksum(name: $name, path: $path);

                $integrity = match (true) {
                    ! $dbChecksum                 => 'LEGACY',
                    $dbChecksum === $fileChecksum => 'OK',
                    default                       => 'TAMPERED',
                };
            }

            $rows[] = [
                'migration' => $name,
                'status'    => $isRan ? 'RAN' : 'PENDING',
                'integrity' => $integrity,
            ];
        }

        return [
            'rows'    => $rows,
            'summary' => [
                'total'   => count(value: $all),
                'ran'     => count(value: $ran),
                'pending' => count(value: $all) - count(value: $ran),
            ],
        ];
    }
}
