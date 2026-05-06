<?php

declare(strict_types=1);

namespace Avax\Components\Application\Facade\System\PublicSurface;

use Avax\Components\Application\Facade\System\Foundation\BaseFacade;
use Avax\Components\Application\Filesystem\System\PublicSurface\FilesystemInterface;

final class StorageFacade extends BaseFacade
{
    protected static string|null $accessor = FilesystemInterface::class;
}
