<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\PublicSurface;

use Avax\Components\Operations\Logging\System\Capabilities\Writing\RotatingFileWriter;

/**
 * Logging Public Surface.
 *
 * Simple entry point for all logging activities.
 * Delegated to internal writers and flows.
 */
final readonly class Log
{
    public function __construct(
        private RotatingFileWriter $rotatingFileWriter,
    ) {
    }

    public function info(string $message, array $context = []): void
    {
        $this->rotatingFileWriter->write($message, 'info', $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->rotatingFileWriter->write($message, 'error', $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->rotatingFileWriter->write($message, 'warning', $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->rotatingFileWriter->write($message, 'debug', $context);
    }
}
