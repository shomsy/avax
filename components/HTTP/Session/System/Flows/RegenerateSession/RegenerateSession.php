<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Flows\RegenerateSession;

use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;

final readonly class RegenerateSession
{
    public function __construct(
        private SessionScope $scope,
    ) {
    }

    public function execute(bool $destroy = false): bool
    {
        return $this->scope->regenerate($destroy);
    }
}
