<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Configuration;

final class RegisterDataLayerRuntime
{
    public function register(object $databaseRuntime): DataLayerConfig
    {
        return new DataLayerConfig();
    }
}
