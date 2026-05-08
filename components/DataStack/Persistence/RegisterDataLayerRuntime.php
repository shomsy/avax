<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence;

final class RegisterDataLayerRuntime
{
    public function register(object $databaseRuntime): DataLayerConfig
    {
        return new DataLayerConfig();
    }
}
