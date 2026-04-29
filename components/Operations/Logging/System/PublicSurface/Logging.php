<?php
declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\PublicSurface;

use Avax\Components\Operations\Logging\System\Capabilities\Writers\RotatingFileWriter;
use Psr\Log\LoggerInterface;
use Stringable;

final readonly class Logging implements LoggerInterface
{
    public function __construct(
        private RotatingFileWriter $writer
    ) {}

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->log('emergency', (string)$message, $context);
    }

    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->log('alert', (string)$message, $context);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->log('critical', (string)$message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->log('error', (string)$message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->log('warning', (string)$message, $context);
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->log('notice', (string)$message, $context);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->log('info', (string)$message, $context);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->log('debug', (string)$message, $context);
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->writer->write((string)$message, (string)$level, $context);
    }
}