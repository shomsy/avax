<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Configuration\ConfigureDataLayer\Builders;

use Avax\Components\DataStack\Persistence\System\Configuration\ConfigureDataLayer\DataLayerConfig;

final class RegisterDataLayerRuntime
{
    public function register(object $databaseRuntime): DataLayerConfig
    {
        return new DataLayerConfig(databaseRuntime: $databaseRuntime);
    }
}
