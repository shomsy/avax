<?php

declare(strict_types=1);

namespace Avax\DataLayer\ConfigureDataLayer;

/**
 * ValidateDataLayerConfig - fails early when the DataLayer would otherwise read hidden global state.
 */
final readonly class ValidateDataLayerConfig
{
    /**
     * @throws DataLayerConfigurationFailure
     */
    public function validate(DataLayerConfig $config) : void
    {
        if ($config->databaseRuntime === null) {
            throw DataLayerConfigurationFailure::missingDatabaseRuntime();
        }
    }
}
