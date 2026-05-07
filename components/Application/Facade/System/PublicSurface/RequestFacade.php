<?php

declare(strict_types=1);

namespace Avax\Components\Application\Facade\System\PublicSurface;

use Avax\Components\Application\Facade\System\Foundation\BaseFacade;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;

final class RequestFacade extends BaseFacade
{
    protected static string|null $accessor = RequestInterface::class;
}
