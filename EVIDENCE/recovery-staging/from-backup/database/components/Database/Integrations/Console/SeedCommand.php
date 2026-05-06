<?php

declare(strict_types=1);

namespace Avax\Database\Integrations\Console;

use Avax\Database\System\Capabilities\Migrations\SeedDatabase\Seeder;
use Throwable;

final readonly class SeedCommand
{
    /**
     * @param  class-string<Seeder>  $seederClass
     */
    public function handle(string $seederClass): int
    {
        try {
            new $seederClass()->run();
            echo "\033[32mSeed completed.\033[0m\n";

            return 0;
        } catch (Throwable $throwable) {
            echo "\033[31mSeed failed:\033[0m {$throwable->getMessage()}\n";

            return 1;
        }
    }
}
