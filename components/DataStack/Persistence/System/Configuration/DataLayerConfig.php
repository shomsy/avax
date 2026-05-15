<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Configuration;

final readonly class DataLayerConfig
{
    public function __construct(
        private object $databaseRuntime
    ) {
    }

    public function databaseRuntime(): object
    {
        return $this->databaseRuntime;
    }
}
