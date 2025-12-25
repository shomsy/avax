<?php

declare(strict_types=1);

namespace Avax\Migrations\Execution\Console;

use Avax\Migrations\Execution\Repository\MigrationRepository;
use Avax\Migrations\Generate\MigrationLoader;

/**
 * Console command to show migration status.
 */
final class MigrateStatusCommand
{
    public function __construct(
        private readonly MigrationRepository $repository,
        private readonly MigrationLoader     $loader
    ) {}

    public function handle(string $path) : int
    {
        $this->info('Migration Status:');
        echo "\n";

        $all = $this->loader->load($path);
        $ran = $this->repository->getRan();

        if (empty($all)) {
            echo "No migrations found.\n";

            return 0;
        }

        echo str_pad('Migration', 50) . " | Status  | Integrity\n";
        echo str_repeat('-', 80) . "\n";

        $ranMap = array_column($ran, 'checksum', 'migration');

        foreach ($all as $name => $migration) {
            $isRan  = isset($ranMap[$name]);
            $status = $isRan ? "\033[32mRAN\033[0m" : "\033[33mPENDING\033[0m";

            $integrity = '---';
            if ($isRan) {
                $dbChecksum   = $ranMap[$name];
                $fileChecksum = $this->loader->getChecksum($name, $path);

                if (! $dbChecksum) {
                    $integrity = "\033[34mLEGACY\033[0m"; // Table created before checksum support
                } elseif ($dbChecksum === $fileChecksum) {
                    $integrity = "\033[32mOK\033[0m";
                } else {
                    $integrity = "\033[31mTAMPERED\033[0m";
                }
            }

            echo str_pad($name, 50) . " | " . str_pad($status, 15) . " | {$integrity}\n";
        }

        echo "\n";
        $this->info(sprintf('Total: %d | Ran: %d | Pending: %d', count($all), count($ran), count($all) - count($ran)));

        return 0;
    }

    private function info(string $msg) : void
    {
        echo "\033[36m{$msg}\033[0m\n";
    }
}
