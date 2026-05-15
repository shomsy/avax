<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\BuildApplication\Builders;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\HTTP\System\Capabilities\ResponseBuilding\ResponseFactory;
use Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp;
use Avax\Framework\System\Flows\RunDoctor\RunDoctor;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Time\SystemClock;

final readonly class BuildApplication
{
    public static function fromProjectPath(
        string $projectPath,
        string $environment = 'production',
    ): ApplicationBuilder {
        return new ApplicationBuilder(
            projectPath       : new ProjectPath(value: $projectPath),
            environmentName   : EnvironmentName::fromString($environment),
            clock             : new SystemClock(),
            runDoctor         : new RunDoctor(),
            handleIncomingHttp: new HandleIncomingHttp(responseFactory: new ResponseFactory()),
            filesystem        : new Filesystem(),
            responseFactory   : new ResponseFactory(),
        );
    }
}
