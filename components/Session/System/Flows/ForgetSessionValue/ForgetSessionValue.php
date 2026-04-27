<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Flows\ForgetSessionValue;

use Avax\Components\Session\System\Capabilities\Storage\SessionScope;

final class ForgetSessionValue
{
    public function __construct(
        private readonly SessionScope $scope,
    ) {
    }

    public function remove(string $key): void
    {
        $this->scope->remove(key: $key);
    }
}