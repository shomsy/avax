<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Flows\AccessPersistentData\Foundation;

final class PersistentDataRequest
{
    public function __construct(
        public string $statement,
        public array $parameters = []
    ) {
    }
}
