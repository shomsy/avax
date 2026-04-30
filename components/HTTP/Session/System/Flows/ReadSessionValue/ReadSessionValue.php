<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Flows\ReadSessionValue;

use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;

final readonly class ReadSessionValue
{
    public function __construct(
        private SessionScope $scope,
    ) {
    }

    public function execute(string $key, mixed $default = null): mixed
    {
        return $this->scope->get($key, $default);
    }
}
