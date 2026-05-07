<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Diagnostics\System\System\Flows;

use Avax\Components\DeveloperTools\Diagnostics\System\System\PublicSurface\Diagnostics;

final class RunDiagnostics
{
    public static function execute() : array
    {
        return [Diagnostics::health()];
    }
}
