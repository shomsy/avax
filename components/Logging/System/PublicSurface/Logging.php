<?php

declare(strict_types=1);

namespace Avax\Components\Logging\System\PublicSurface;

use Avax\Components\Logging\System\Capabilities\Logger\ErrorLogger;
use Avax\Components\Logging\System\Capabilities\Writers\FileLogWriter;
use Psr\Log\LoggerInterface;
use Stringable;

/**
 * Public surface for the Logging component.
 */
final class Logging implements LoggerInterface
{
    private LoggerInterface $logger;

    public function __construct(?string $logPath = null)
    {
        $path         = $logPath ?? '/home/shomsy/projects/avax/storage/logs/avax.log';
        $writer       = new FileLogWriter($path);
        $this->logger = new ErrorLogger($writer);
    }

    public function emergency(Stringable|string $message, array $context = []) : void
    {
        $this->logger->emergency($message, $context);
    }

    public function alert(Stringable|string $message, array $context = []) : void
    {
        $this->logger->alert($message, $context);
    }

    public function critical(Stringable|string $message, array $context = []) : void
    {
        $this->logger->critical($message, $context);
    }

    public function error(Stringable|string $message, array $context = []) : void
    {
        $this->logger->error($message, $context);
    }

    public function warning(Stringable|string $message, array $context = []) : void
    {
        $this->logger->warning($message, $context);
    }

    public function notice(Stringable|string $message, array $context = []) : void
    {
        $this->logger->notice($message, $context);
    }

    public function info(Stringable|string $message, array $context = []) : void
    {
        $this->logger->info($message, $context);
    }

    public function debug(Stringable|string $message, array $context = []) : void
    {
        $this->logger->debug($message, $context);
    }

    public function log(mixed $level, Stringable|string $message, array $context = []) : void
    {
        $this->logger->log($level, $message, $context);
    }
}