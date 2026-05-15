<?php

declare(strict_types=1);

namespace Avax\Examples\GoldenPathRuntimeApp;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\HTTP\System\Capabilities\ResponseBuilding\ResponseFactory;
use Avax\Framework\System\Configuration\BuildApplication\Builders\ApplicationBuilder;
use Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp;
use Avax\Framework\System\Flows\RunDoctor\RunDoctor;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Time\SystemClock;

/**
 * Assembles the Webhook Ingestion Pipeline application.
 *
 * Provides a pre-configured ApplicationBuilder with route definitions.
 * The caller boots the runtime via Avax::boot($builder).
 *
 * MessageBus handlers, Queue jobs, and resilience configuration
 * are applied per-test or per-request to avoid static state pollution.
 */
final class WebhookIngestionApp
{
    public static function createBuilder(ProjectPath $projectPath, EnvironmentName $environmentName) : ApplicationBuilder
    {
        return (new ApplicationBuilder(
            projectPath       : $projectPath,
            environmentName   : $environmentName,
            clock             : new SystemClock(),
            runDoctor         : new RunDoctor(),
            handleIncomingHttp: new HandleIncomingHttp(responseFactory: new ResponseFactory()),
            filesystem        : new Filesystem(),
            responseFactory   : new ResponseFactory(),
        ))->withHttpRoutes(__DIR__ . '/config/routes.php');
    }
}
