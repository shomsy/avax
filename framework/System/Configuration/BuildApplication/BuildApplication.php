<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\BuildApplication;

use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;

final readonly class BuildApplication
{
    public static function fromProjectPath(
        string $projectPath,
        string $environment = 'production',
    ): ApplicationBuilder {
        return new ApplicationBuilder(
            projectPath: new ProjectPath(value: $projectPath),
            environmentName: EnvironmentName::fromString($environment),
        );
    }
}
