<?php
declare(strict_types=1);

namespace Avax\Components\DataLayer\ConfigureDataLayer;

use Avax\Components\Persistence\System\Configuration\PersistenceBuilder;

final class RegisterDataLayerRuntime
{
    public function register(object $databaseRuntime) : DataLayerConfig
    {
        return new DataLayerConfig(databaseRuntime: $databaseRuntime);
    }
}
