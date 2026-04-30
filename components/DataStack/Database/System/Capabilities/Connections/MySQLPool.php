<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections;

use Override;
use PDO;

final class MySQLPool implements ConnectionPool
{
    private array $pool = [];

    public function __construct(
        private readonly string $dsn,
        private readonly string $username,
        private readonly string $password,
        private readonly array $options = [],
    ) {}

    #[Override]
    public function get() : PDO
    {
        if ($this->pool !== []) {
            return array_pop($this->pool);
        }

        return new PDO($this->dsn, $this->username, $this->password, $this->options);
    }

    #[Override]
    public function release(PDO $pdo) : void
    {
        $this->pool[] = $pdo;
    }
}
