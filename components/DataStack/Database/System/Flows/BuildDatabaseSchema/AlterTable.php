<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Flows\BuildDatabaseSchema;

use Avax\Components\DataStack\Database\Schema\Blueprint;

final class AlterTable
{
    public static function execute(string $table, callable $define): Blueprint
    {
        $bp = new Blueprint($table);
        $define($bp);

        return $bp;
    }
}
