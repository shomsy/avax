<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Capabilities\ErrorHandling;

use Avax\Components\Operations\Logging\System\PublicSurface\Logging;
use Avax\Components\Security\Redaction\System\PublicSurface\Redaction;
use DateTimeImmutable;
use DateTimeZone;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;
use Throwable;

/**
 * Structured error logger with context enrichment, secret redaction, and correlation tracking.
 *
 * Provides PSR-3 compatible logging with automatic redaction of sensitive data
 * and correlation ID propagation.
 */
final class ErrorLogger implements LoggerInterface
{
    /** @var list<string> */
    private static array $defaultSensitiveKeys
        = [
            'password', 'passwd', 'pass', 'pwd', 'secret', 'secret_key',
            'api_key', 'apikey', 'api-key', 'access_token', 'access-token',
            'refresh_token', 'refresh-token', 'auth_token', 'auth-token',
            'authorization', 'token', 'bearer', 'jwt', 'private_key', 'private-key',
            'credit_card', 'creditcard', 'card_number', 'cardnumber', 'cvv', 'cvc',
            'card_cvv', 'ssn', 'social_security', 'social-security-number',
            'database_url', 'database-password', 'db_password', 'db-pass',
            'encryption_key', 'encryption-key',
        ];

    /**
     * @param  Logging  $logging  The underlying logging facade
     * @param  string|null  $correlationId  Current correlation ID for request tracking
     * @param  string|null  $traceId  Current trace ID for distributed tracing
     */
    public function __construct(
        private Logging $logging,
        private string|null $correlationId = null,
        private string|null $traceId = null,
    ) {
    }

    public function emergency(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $level = (string) $level;
        $message = (string) $message;

        $context = $this->enrichContext($context);
        $context = $this->redactContext($context);

        $this->logging->log($level, $message, $context);
    }

    /**
     * Enrich context with correlation and trace IDs.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function enrichContext(array $context): array
    {
        if ($this->correlationId !== null) {
            $context['correlation_id'] = $this->correlationId;
        }

        if ($this->traceId !== null) {
            $context['trace_id'] = $this->traceId;
        }

        $context['timestamp'] = new DateTimeImmutable('now', new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.uP');

        return $context;
    }

    /**
     * Redact sensitive data from context.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function redactContext(array $context): array
    {
        return Redaction::redactLog(
            logData      : $context,
            sensitiveKeys: self::$defaultSensitiveKeys,
        );
    }

    public function alert(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Log an exception with full structured context.
     *
     * @param  array<string, mixed>  $additionalContext
     */
    public function logException(
        string $level,
        Throwable $throwable,
        array $additionalContext = [],
    ): void {
        $context = $this->buildExceptionContext($throwable, $additionalContext);

        $message = sprintf(
            '%s: %s',
            $throwable::class,
            $throwable->getMessage(),
        );

        $this->log($level, $message, $context);
    }

    /**
     * Build a structured context array from an exception.
     *
     * @param  array<string, mixed>  $additionalContext
     * @return array<string, mixed>
     */
    private function buildExceptionContext(Throwable $throwable, array $additionalContext = []): array
    {
        $context = [
            'exception' => [
                'class' => $throwable::class,
                'message' => $throwable->getMessage(),
                'code' => $throwable->getCode(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'trace' => $this->formatTrace($throwable),
            ],
        ];

        if ($throwable->getPrevious() instanceof Throwable) {
            $context['exception']['previous'] = $this->buildExceptionContext($throwable->getPrevious());
        }

        return array_merge($context, $additionalContext);
    }

    /**
     * Format exception trace as structured data.
     *
     * @return list<array{file: string, line: int, class: string|null, type: string|null, function: string, args:
     *                          list<string>}>
     */
    private function formatTrace(Throwable $throwable): array
    {
        $trace = [];

        foreach ($throwable->getTrace() as $frame) {
            $args = [];

            if (isset($frame['args'])) {
                foreach ($frame['args'] as $arg) {
                    $args[] = $this->formatArgument($arg);
                }
            }

            $trace[] = [
                'file' => $frame['file'] ?? '[internal]',
                'line' => $frame['line'] ?? 0,
                'class' => $frame['class'] ?? null,
                'type' => $frame['type'] ?? null,
                'function' => $frame['function'],
                'args' => $args,
            ];
        }

        return $trace;
    }

    /**
     * Format a function argument for safe logging (redacted).
     */
    private function formatArgument(mixed $arg): string
    {
        if ($arg === null) {
            return 'null';
        }

        if (is_bool($arg)) {
            return $arg ? 'true' : 'false';
        }

        if (is_int($arg) || is_float($arg)) {
            return (string) $arg;
        }

        if (is_string($arg)) {
            $truncated = mb_strlen($arg) > 100 ? mb_substr($arg, 0, 100).'...' : $arg;
            $redacted = Redaction::redactLog(
                logData      : ['value' => $truncated],
                sensitiveKeys: self::$defaultSensitiveKeys,
            );

            return $redacted['value'];
        }

        if (is_array($arg)) {
            $redacted = Redaction::redactLog(
                logData      : $arg,
                sensitiveKeys: self::$defaultSensitiveKeys,
            );

            $encoded = json_encode($redacted, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            return $encoded !== false ? $encoded : '[array]';
        }

        if (is_object($arg)) {
            return $arg::class;
        }

        if (is_resource($arg)) {
            return get_resource_type($arg).' resource';
        }

        return gettype($arg);
    }
}
