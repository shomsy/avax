<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Configuration\Builders;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Operations\Logging\System\Capabilities\Writing\RotatingFileWriter;
use Avax\Components\Operations\Logging\System\PublicSurface\Log;

/**
 * Configuration unit to register and assemble Logging services.
 */
final class RegisterLogging
{
    public function build(string $channel = 'app'): Log
    {
        $logPath = storage_path('logs/'.$channel);

        return new Log(
            rotatingFileWriter: new RotatingFileWriter(
                baseLogPath: $logPath,
                filesystem : new Filesystem(),
                timezone   : config('app.timezone', 'UTC'),
            ),
        );
    }
}
