<?php

declare(strict_types=1);

namespace Avax\DataLayer\ConfigureDataLayer;

/**
 * DataLayerRuntime - resolved runtime dependencies that every data capability receives explicitly.
 */
final readonly class DataLayerRuntime
{
    public function __construct(public object $databaseRuntime) {}
}
