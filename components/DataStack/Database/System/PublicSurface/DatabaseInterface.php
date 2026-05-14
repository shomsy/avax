<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\PublicSurface;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\ConnectionContracts\DatabaseConnection;

interface DatabaseInterface
{
    public function connection(string $name = 'default'): DatabaseConnection;

    public function transactions() : Transactions;

    public function schema() : Schema;
}
