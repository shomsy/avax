<?php
declare(strict_types=1);
namespace Avax\DataLayer\ConfigureDataLayer;

use Avax\DataLayer\ConfigureDataLayer\DataLayerConfig;

final class RegisterDataLayerRuntime
{
    public function register(object $databaseRuntime): DataLayerConfig
    {
        return new DataLayerConfig(databaseRuntime: $databaseRuntime);
    }
}
