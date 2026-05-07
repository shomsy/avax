<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\System\System\Capabilities\DataLayer\AccessPersistentData;

final class PersistentDataRequest
{
    public function __construct(
        public string $statement,
        public array  $parameters = []
    ) {}
}
