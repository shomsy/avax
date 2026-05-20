<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\CLI;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\SeedDatabase\Seeder;
use RuntimeException;

final class SeederCommand
{
    public function __construct(
        private Filesystem $filesystem,
    ) {}

    public function run(): array
    {
        $ran = [];
        $path = dirname(__DIR__, 6).'/database/seeders';

        if (! $this->filesystem->isDirectory($path)) {
            return ['status' => 'nothing', 'message' => 'No seeders found'];
        }

        $files = $this->filesystem->listFilesByPattern($path . '/*Seeder.php');
        foreach ($files as $file) {
            require_once $file;
            $className = str_replace([$path.'/', '.php'], '', $file);

            if (!class_exists($className) || !is_subclass_of($className, Seeder::class)) {
                throw new RuntimeException("Seeder class [{$className}] does not exist or does not extend Seeder.");
            }

            $seeder = new $className();
            $seeder->run();

            $ran[] = $className;
        }

        return [
            'status' => 'success',
            'ran' => $ran,
        ];
    }
}
