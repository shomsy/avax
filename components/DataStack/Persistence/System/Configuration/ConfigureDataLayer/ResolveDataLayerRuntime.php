<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Configuration\ConfigureDataLayer;

final class ResolveDataLayerRuntime
{
    public function resolve(DataLayerConfig $config): object
    {
        return $config->databaseRuntime();
    }
}
