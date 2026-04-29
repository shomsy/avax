<?php
declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections;

use PDO;

interface ConnectionPool
{
    public function get(): PDO;
    public function release(PDO $pdo): void;
}
