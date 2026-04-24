<?php

declare(strict_types=1);

namespace Avax\DataLayer\ConfigureDataLayer;

/**
 * DataLayerConfig - immutable input used to compose the DataLayer without reading global state.
 */
final readonly class DataLayerConfig
{
    public function __construct(
        public object|null $databaseRuntime = null,
        public bool        $allowRawQueries = true
    ) {}

    public function withDatabaseRuntime(object $databaseRuntime) : self
    {
        return new self(databaseRuntime: $databaseRuntime, allowRawQueries: $this->allowRawQueries);
    }
}
