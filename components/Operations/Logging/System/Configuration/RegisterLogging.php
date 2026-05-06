<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Configuration;

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
            writer: new RotatingFileWriter(
                baseLogPath: $logPath,
                timezone   : config('app.timezone', 'UTC'),
            ),
        );
    }
}
