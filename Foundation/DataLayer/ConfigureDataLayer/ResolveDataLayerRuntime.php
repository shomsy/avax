<?php

declare(strict_types=1);

namespace Avax\DataLayer\ConfigureDataLayer;

/**
 * ResolveDataLayerRuntime - turns validated configuration into runtime dependencies for data capabilities.
 */
final readonly class ResolveDataLayerRuntime
{
    public function __construct(private ValidateDataLayerConfig $validateDataLayerConfig = new ValidateDataLayerConfig()) {}

    /**
     * @throws DataLayerConfigurationFailure
     */
    public function resolve(DataLayerConfig $config) : DataLayerRuntime
    {
        $this->validateDataLayerConfig->validate(config: $config);

        return new DataLayerRuntime(databaseRuntime: $config->databaseRuntime);
    }
}
