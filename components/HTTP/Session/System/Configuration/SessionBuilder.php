<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Configuration;

use Avax\Components\HTTP\Session\System\Capabilities\Storage\NativeSessionStore;
use Avax\Components\HTTP\Session\System\PublicSurface\Session;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;

final class SessionBuilder
{
    public function build() : Session
    {
        $store = new NativeSessionStore();
        $scope = new SessionScope($store);

        return new Session($scope);
    }
}
