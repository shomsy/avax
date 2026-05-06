<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\ConfigureDataLayer;

final class ResolveDataLayerRuntime
{
    public function resolve(DataLayerConfig $config): object
    {
        return $config->databaseRuntime();
    }
}
