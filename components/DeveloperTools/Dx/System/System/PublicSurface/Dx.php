<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Dx\System\System\PublicSurface;

use Avax\Components\DeveloperTools\Dx\System\System\Capabilities\Commands\DxCommand;
use Avax\Components\DeveloperTools\Dx\System\System\Capabilities\Templates\ProjectTemplate;

final readonly class Dx
{
    public static function command(string $name) : DxCommand
    {
        return new DxCommand($name);
    }

    public static function template(string $name) : ProjectTemplate
    {
        return new ProjectTemplate($name);
    }
}
