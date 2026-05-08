<?php

declare(strict_types=1);

namespace Avax\Examples\GoldenPathRuntimeApp;

use Avax\Framework\System\Configuration\BuildApplication\ApplicationBuilder;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;

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
            projectPath    : $projectPath,
            environmentName: $environmentName,
        ))->withHttpRoutes(__DIR__ . '/config/routes.php');
    }
}
