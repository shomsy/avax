<?php

declare(strict_types=1);

namespace Avax\Components\Application\Facade\System\PublicSurface;

use Avax\Components\Application\Facade\System\Foundation\BaseFacade;
use Avax\Components\Identity\Auth\System\Auth as AuthContract;

final class Auth extends BaseFacade
{
    protected static string|null $accessor = AuthContract::class;
}
