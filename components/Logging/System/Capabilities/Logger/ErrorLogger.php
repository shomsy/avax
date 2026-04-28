<?php

declare(strict_types=1);

namespace Avax\Components\Logging\System\Capabilities\Logger;

use Avax\Components\Logging\System\Capabilities\Writers\LogWriterInterface;
use DateTimeImmutable;
use DateTimeZone;
use JsonException;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;
use Throwable;

/**
 * PSR-3 compliant logger that uses a LogWriter for persistence.
 */
final readonly class ErrorLogger implements LoggerInterface
{
    public function __construct(
        private LogWriterInterface $writer
    ) {}

    public function emergency(Stringable|string $message, array $context = []) : void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function log(mixed $level, Stringable|string $message, array $context = []) : void
    {
        if (! is_string($level)) {
            throw new InvalidArgumentException("Log level must be a string.");
        }

        $timestamp = (new DateTimeImmutable('now', new DateTimeZone('Europe/Belgrade')))
            ->format('Y-m-d H:i:s');

        $entry = sprintf(
            "[%s] %s %s %s",
            $timestamp,
            strtoupper($level),
            (string) $message,
            $this->formatContext($context)
        );

        $this->writer->write($entry);
    }

    private function formatContext(array $context) : string
    {
        if (empty($context)) {
            return '';
        }

        if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
            $e                    = $context['exception'];
            $context['exception'] = [
                'class'   => $e::class,
                'message' => $e->getMessage(),
                'file'    => $e->getFile() . ':' . $e->getLine(),
                'trace'   => array_slice(explode("\n", $e->getTraceAsString()), 0, 10),
            ];
        }

        try {
            return json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (JsonException) {
            return '[Failed to encode context]';
        }
    }

    public function alert(Stringable|string $message, array $context = []) : void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical(Stringable|string $message, array $context = []) : void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error(Stringable|string $message, array $context = []) : void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning(Stringable|string $message, array $context = []) : void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice(Stringable|string $message, array $context = []) : void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info(Stringable|string $message, array $context = []) : void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug(Stringable|string $message, array $context = []) : void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
}
