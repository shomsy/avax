<?php
declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\ConfigureDataLayer;

final class DataLayerConfig
{
    public function __construct(
        private object $databaseRuntime
    )
    {
    }

    public function databaseRuntime(): object
    {
        return $this->databaseRuntime;
    }
}
