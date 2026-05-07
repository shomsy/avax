<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\System\Capabilities\DataLayer\ConfigureDataLayer;

final class RegisterDataLayerRuntime
{
    public function register(object $databaseRuntime) : DataLayerConfig
    {
        return new DataLayerConfig(databaseRuntime: $databaseRuntime);
    }
}
