<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Flows\StoreSessionValue;

use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;

final readonly class StoreSessionValue
{
    public function __construct(
        private SessionScope $scope,
    ) {
    }

    public function execute(string $key, mixed $value): void
    {
        $this->scope->set($key, $value);
    }
}
