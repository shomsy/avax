<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Dx\System\PublicSurface;

use Avax\Components\DeveloperTools\Dx\System\Capabilities\Commands\DxCommand;
use Avax\Components\DeveloperTools\Dx\System\Capabilities\Templates\ProjectTemplate;

final readonly class Dx
{
    public static function command(string $name): DxCommand
    {
        return new DxCommand($name);
    }

    public static function template(string $name): ProjectTemplate
    {
        return new ProjectTemplate($name);
    }
}
