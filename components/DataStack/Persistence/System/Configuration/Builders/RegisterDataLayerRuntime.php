<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Configuration\Builders;

use Avax\Components\DataStack\Persistence\System\Configuration\DataLayerConfig;

final class RegisterDataLayerRuntime
{
    public function register(object $databaseRuntime): DataLayerConfig
    {
        return new DataLayerConfig(databaseRuntime: $databaseRuntime);
    }
}
