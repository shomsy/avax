<?php

declare(strict_types=1);

namespace components\DataLayer\ConfigureDataLayer;

use RuntimeException;

/**
 * DataLayerConfigurationFailure - reports an invalid DataLayer composition boundary.
 */
final class DataLayerConfigurationFailure extends RuntimeException
{
    public static function missingDatabaseRuntime() : self
    {
        return new self(message: 'DataLayer requires an explicit Foundation/Database runtime. Pass it through DataLayerConfig::withDatabaseRuntime() or DataLayer::fromDatabaseRuntime().');
    }
}
