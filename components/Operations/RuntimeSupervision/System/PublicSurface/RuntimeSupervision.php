<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\PublicSurface;

use Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Process\ProcessRegistry;
use Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Supervision\Supervisor;

final readonly class RuntimeSupervision
{
    public static function supervisor(string $name): Supervisor
    {
        return new Supervisor($name);
    }

    public static function registry(): ProcessRegistry
    {
        return new ProcessRegistry();
    }
}
