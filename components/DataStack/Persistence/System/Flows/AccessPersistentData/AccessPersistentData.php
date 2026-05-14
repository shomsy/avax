<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Flows\AccessPersistentData;

final readonly class AccessPersistentData
{
    public function __construct(
        private object $databaseRuntime,
    ) {
    }
}
