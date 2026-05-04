<?php
declare(strict_types=1);
namespace Avax\DataLayer;

use Avax\Components\Persistence\System\Capabilities\Repositories\Repository;
use Avax\Components\Persistence\System\Capabilities\UnitOfWork\UnitOfWork;
use Avax\Components\Persistence\System\Configuration\PersistenceBuilder;

final class DataLayer
{
    private AccessPersistentData $access;
    private CommitDataChanges $commit;
    private DataLayerConfig $config;

    private function __construct(AccessPersistentData $access, CommitDataChanges $commit, DataLayerConfig $config)
    {
        $this->access = $access;
        $this->commit = $commit;
        $this->config = $config;
    }

    public static function fromDatabaseRuntime(object $databaseRuntime): self
    {
        $config = (new RegisterDataLayerRuntime())->register(databaseRuntime: $databaseRuntime);
        $access = new AccessPersistentData(databaseRuntime: $databaseRuntime);
        $commit = new CommitDataChanges(databaseRuntime: $databaseRuntime);
        return new self(access: $access, commit: $commit, config: $config);
    }

    public function access(): AccessPersistentData
    {
        return $this->access;
    }

    public function commit(): CommitDataChanges
    {
        return $this->commit;
    }

    public function configuration(): DataLayerConfig
    {
        return $this->config;
    }
}
