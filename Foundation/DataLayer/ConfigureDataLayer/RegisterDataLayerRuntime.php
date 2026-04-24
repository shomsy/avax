<?php

declare(strict_types=1);

namespace Avax\DataLayer\ConfigureDataLayer;

/**
 * RegisterDataLayerRuntime - records the concrete Foundation/Database runtime in DataLayer configuration.
 */
final readonly class RegisterDataLayerRuntime
{
    public function register(object $databaseRuntime, DataLayerConfig|null $config = null) : DataLayerConfig
    {
        return ($config ?? new DataLayerConfig())->withDatabaseRuntime(databaseRuntime: $databaseRuntime);
    }
}
