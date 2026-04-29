<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Configuration;

use Avax\Components\HTTP\Session\System\PublicSurface\Session;

final class SessionBuilder
{
    public function build(): Session
    {
        return new Session(...); // Placeholder
    }
}
