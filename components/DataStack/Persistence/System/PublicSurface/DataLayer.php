<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\PublicSurface;

use Avax\Components\DataStack\Persistence\System\Configuration\DataLayerConfig;
use Avax\Components\DataStack\Persistence\System\Configuration\RegisterDataLayerRuntime;
use Avax\Components\DataStack\Persistence\System\Flows\AccessPersistentData\AccessPersistentData;
use Avax\Components\DataStack\Persistence\System\Flows\CommitDataChanges\CommitDataChanges;

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
