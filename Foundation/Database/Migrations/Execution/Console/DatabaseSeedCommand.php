<?php

declare(strict_types=1);

namespace Avax\Migrations\Execution\Console;

use Throwable;

/**
 * Console command to run database seeders.
 */
final class DatabaseSeedCommand
{
    public function handle(string $seedersPath, ?string $class = 'DatabaseSeeder') : int
    {
        $class = $class ?: 'DatabaseSeeder';
        $file  = rtrim($seedersPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $class . '.php';

        if (! file_exists($file)) {
            echo "\033[31mSeeder file not found: {$file}\033[0m\n";

            return 1;
        }

        echo "\033[36mRunning seeder: {$class}...\033[0m\n";

        try {
            require_once $file;
            $seeder = new $class();
            $seeder->run();

            echo "\033[32mDatabase seeding completed successfully!\033[0m\n";

            return 0;
        } catch (Throwable $e) {
            echo "\033[31mSeeding failed: {$e->getMessage()}\033[0m\n";

            return 1;
        }
    }
}
