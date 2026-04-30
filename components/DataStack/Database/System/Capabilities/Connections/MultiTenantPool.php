<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections;

use Override;
use PDO;
use RuntimeException;

final class MultiTenantPool implements ConnectionPool
{
    private array $pools = [];

    public function __construct(private readonly array $tenantConfigs) {}

    public function getForTenant(string $tenantId) : PDO
    {
        if (! isset($this->pools[$tenantId])) {
            $config = $this->tenantConfigs[$tenantId];
            $this->pools[$tenantId] = new PDO($config['dsn'], $config['username'], $config['password']);
        }

        return $this->pools[$tenantId];
    }

    #[Override]
    public function get() : PDO
    {
        throw new RuntimeException('Use getForTenant() for multi-tenant pool.');
    }

    #[Override]
    public function release(PDO $pdo) : void {}
}
