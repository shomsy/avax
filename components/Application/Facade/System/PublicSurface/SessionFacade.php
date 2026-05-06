<?php

declare(strict_types=1);

namespace Avax\Components\Application\Facade\System\PublicSurface;

use Avax\Components\Application\Facade\System\Foundation\BaseFacade;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionInterface;

final class SessionFacade extends BaseFacade
{
    protected static string|null $accessor = SessionInterface::class;
}
