<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Flows\ReadSessionValue;

use Avax\Components\Session\System\Capabilities\Storage\SessionScope;

final class ReadSessionValue
{
    public function __construct(
        private readonly SessionScope $scope,
    ) {
    }

    public function read(string $key): mixed
    {
        return $this->scope->read(key: $key);
    }
}