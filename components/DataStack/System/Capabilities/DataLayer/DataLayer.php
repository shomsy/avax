<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\System\Capabilities\DataLayer;

use Avax\Components\DataStack\System\Capabilities\DataLayer\ConfigureDataLayer\DataLayerConfig;
use Avax\Components\DataStack\System\Capabilities\DataLayer\ConfigureDataLayer\RegisterDataLayerRuntime;

final readonly class DataLayer
{
    private function __construct(
        private AccessPersistentData $accessPersistentData,
        private CommitDataChanges    $commitDataChanges,
        private DataLayerConfig      $dataLayerConfig,
    )
    {
    }

    public static function fromDatabaseRuntime(object $databaseRuntime): self
    {
        $dataLayerConfig = new RegisterDataLayerRuntime()->register(databaseRuntime: $databaseRuntime);
        $accessPersistentData = new AccessPersistentData(databaseRuntime: $databaseRuntime);
        $commitDataChanges = new CommitDataChanges(databaseRuntime: $databaseRuntime);

        return new self(access: $accessPersistentData, commit: $commitDataChanges, config: $dataLayerConfig);
    }

    public function access(): AccessPersistentData
    {
        return $this->accessPersistentData;
    }

    public function commit(): CommitDataChanges
    {
        return $this->commitDataChanges;
    }

    public function configuration(): DataLayerConfig
    {
        return $this->dataLayerConfig;
    }
}
