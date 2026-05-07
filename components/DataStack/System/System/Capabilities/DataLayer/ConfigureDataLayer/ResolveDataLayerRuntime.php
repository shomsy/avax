<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\System\System\Capabilities\DataLayer\ConfigureDataLayer;

final class ResolveDataLayerRuntime
{
    public function resolve(DataLayerConfig $dataLayerConfig) : object
    {
        return $dataLayerConfig->databaseRuntime();
    }
}
