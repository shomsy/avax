<?php
declare(strict_types=1);
namespace Avax\DataLayer\ConfigureDataLayer;

use Avax\DataLayer\ConfigureDataLayer\DataLayerConfig;

final class ResolveDataLayerRuntime
{
    public function resolve(DataLayerConfig $config): object
    {
        return $config->databaseRuntime();
    }
}
