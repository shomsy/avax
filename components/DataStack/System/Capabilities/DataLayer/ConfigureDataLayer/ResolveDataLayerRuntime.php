<?php
declare(strict_types=1);

namespace Avax\Components\DataStack\System\Capabilities\DataLayer\ConfigureDataLayer;

use Avax\Components\DataStack\Persistence\System\Configuration\PersistenceBuilder;

final class ResolveDataLayerRuntime
{
    public function resolve(DataLayerConfig $dataLayerConfig): object
    {
        return $dataLayerConfig->databaseRuntime();
    }
}
