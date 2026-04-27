<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Flows\StoreSessionValue;

use Avax\Components\Session\System\Capabilities\Storage\SessionScope;

final class StoreSessionValue
{
    public function __construct(
        private readonly SessionScope $scope,
    ) {
    }

    public function write(string $key, mixed $value): void
    {
        $this->scope->write(key: $key, value: $value);
    }
}