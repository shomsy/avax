<?php

declare(strict_types=1);

namespace Avax\Components\DataLayer\AccessPersistentData;

final class PersistentDataRequest
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        public string $statement,
        public array  $parameters = []
    ) {}
}
