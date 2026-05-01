<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\ReadMigrationStatus;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\LoadMigrations\MigrationLoader;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RunMigrations\MigrationRepository;
use Throwable;

/**
 * Reads current migration status, pending state, and checksum integrity.
 */
final readonly class ReadMigrationStatus
{
    public function __construct(
        private MigrationRepository $migrationRepository,
        private MigrationLoader     $migrationLoader,
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
        $all                  = $this->migrationLoader->load(path: $path);
        $ran                  = $this->migrationRepository->getRan();
        $ranMap = array_column(array: $ran, column_key: 'checksum', index_key: 'migration');
        $rows   = [];

        foreach (array_keys($all) as $name) {
            $isRan     = isset($ranMap[$name]);
            $integrity = '---';

            if ($isRan) {
                $dbChecksum   = $ranMap[$name];
                $fileChecksum = $this->migrationLoader->getChecksum(name: $name, path: $path);

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
