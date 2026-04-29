<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Flows\ForgetSessionValue;

use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;

final readonly class ForgetSessionValue
{
    public function __construct(
        private SessionScope $scope,
    ) {
    }

    public function execute(string $key): void
    {
        $this->scope->forget($key);
    }
}
