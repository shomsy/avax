<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence;

final readonly class AccessPersistentData
{
    public function __construct(
        private object $databaseRuntime,
    ) {
    }
}
