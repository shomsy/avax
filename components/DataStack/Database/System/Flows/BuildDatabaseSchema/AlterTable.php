<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Flows\BuildDatabaseSchema;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Table\Blueprint;

final class AlterTable
{
    public static function execute(string $table, callable $define): Blueprint
    {
        $blueprint = new Blueprint($table);
        $define($blueprint);

        return $blueprint;
    }
}
