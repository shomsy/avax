<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\CLI;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\SchemaBuilder;

final class SeederCommand
{
    public function run(): array
    {
        $ran = [];
        $path = dirname(__DIR__, 6) . '/database/seeders';
        if (!is_dir($path)) {
            return ['status' => 'nothing', 'message' => 'No seeders found'];
        }

        $files = glob($path . '/*Seeder.php');
        foreach ($files as $file) {
            require_once $file;
            $className = str_replace([$path . '/', '.php'], '', $file);
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
