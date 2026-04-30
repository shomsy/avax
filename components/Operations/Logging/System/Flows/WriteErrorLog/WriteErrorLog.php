<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Flows\WriteErrorLog;

use Avax\Components\Operations\Logging\System\Capabilities\Redaction\SecretRedactor;
use Avax\Components\Operations\Logging\System\PublicSurface\Logging;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

/**
 * Flow class for writing structured error records.
 *
 * Produces JSON-structured error log entries with full context including
 * correlation IDs, request information, and redacted sensitive data.
 */
final readonly class WriteErrorLog
{
    public function __construct(
        private Logging $logger,
        private SecretRedactor $redactor = new SecretRedactor(),
        private ?string $correlationId = null,
        private ?string $traceId = null,
    ) {}

    /**
     * Write a structured error log entry for an exception.
     *
     * @param string $level PSR-3 log level
     * @param array<string, mixed> $additionalContext Additional context beyond exception data
     *
     * @return array<string, mixed> The structured log record that was written
     */
    public function writeException(string $level, Throwable $exception, array $additionalContext = []) : array
    {
        $context = array_merge($additionalContext, [
            'exception' => $this->buildExceptionData($exception),
        ]);

        $message = sprintf(
            '%s: %s',
            $exception::class,
            $exception->getMessage(),
        );

        return $this->write($level, $message, $context);
    }

    /**
     * Build structured exception data.
     *
     * @return array<string, mixed>
     */
    private function buildExceptionData(Throwable $exception) : array
    {
        $data = [
            'class'   => $exception::class,
            'message' => $exception->getMessage(),
            'code'    => $exception->getCode(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'trace'   => $this->buildTrace($exception),
        ];

        if ($exception->getPrevious() !== null) {
            $data['previous'] = $this->buildExceptionData($exception->getPrevious());
        }

        return $data;
    }

    /**
     * Build structured trace data.
     *
     * @return list<array{file: string, line: int, class: string|null, type: string|null, function: string}>
     */
    private function buildTrace(Throwable $exception) : array
    {
        $trace = [];

        foreach ($exception->getTrace() as $frame) {
            $trace[] = [
                'file' => $frame['file'] ?? '[internal]',
                'line' => $frame['line'] ?? 0,
                'class'    => $frame['class'] ?? null,
                'type' => $frame['type'] ?? null,
                'function' => $frame['function'],
            ];
        }

        return $trace;
    }

    /**
     * Write a structured error log entry.
     *
     * @param string $level   PSR-3 log level (emergency, alert, critical, error, warning, notice, info,
     *                        debug)
     * @param string $message Log message
     * @param array<string, mixed> $context Additional context data
     *
     * @return array<string, mixed> The structured log record that was written
     */
    public function write(string $level, string $message, array $context = []) : array
    {
        $record = $this->buildRecord($level, $message, $context);
        $record = $this->redactRecord($record);

        $this->logger->log(
            $record['level'],
            $record['message'],
            $record['context'],
        );

        return $record;
    }

    /**
     * Build a structured log record.
     *
     * @param string $level   PSR-3 log level
     * @param string $message Log message
     * @param array<string, mixed> $context Additional context
     *
     * @return array{level: string, message: string, context: array<string, mixed>}
     */
    private function buildRecord(string $level, string $message, array $context) : array
    {
        $context = $this->enrichContext($context);

        return [
            'level'   => strtolower($level),
            'message' => $message,
            'context' => $context,
        ];
    }

    /**
     * Enrich context with system and request metadata.
     *
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    private function enrichContext(array $context) : array
    {
        $timestamp = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $enriched = [
            'timestamp'      => $timestamp->format('Y-m-d\TH:i:s.uP'),
            'unix_timestamp' => (int) $timestamp->format('Uu'),
        ];

        if ($this->correlationId !== null) {
            $enriched['correlation_id'] = $this->correlationId;
        }

        if ($this->traceId !== null) {
            $enriched['trace_id'] = $this->traceId;
        }

        $requestInfo = $this->getRequestInfo();
        if ($requestInfo !== []) {
            $enriched['request'] = $requestInfo;
        }

        $serverInfo = $this->getServerInfo();
        if ($serverInfo !== []) {
            $enriched['server'] = $serverInfo;
        }

        return array_merge($enriched, $context);
    }

    /**
     * Extract request information from the global scope.
     *
     * @return array<string, mixed>
     */
    private function getRequestInfo() : array
    {
        $info = [];

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $info['method'] = $_SERVER['REQUEST_METHOD'];
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $info['uri'] = $_SERVER['REQUEST_URI'];
        }

        if (isset($_SERVER['HTTP_HOST'])) {
            $info['host'] = $_SERVER['HTTP_HOST'];
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $info['client_ip'] = $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            $info['referer'] = $_SERVER['HTTP_REFERER'];
        }

        return $info;
    }

    /**
     * Extract server/environment information.
     *
     * @return array<string, mixed>
     */
    private function getServerInfo() : array
    {
        return [
            'php_version'  => PHP_VERSION,
            'sapi'         => PHP_SAPI,
            'memory_usage' => memory_get_usage(true),
            'memory_peak'  => memory_get_peak_usage(true),
        ];
    }

    /**
     * Redact sensitive data from the log record.
     *
     * @param array{level: string, message: string, context: array<string, mixed>} $record
     *
     * @return array{level: string, message: string, context: array<string, mixed>}
     */
    private function redactRecord(array $record) : array
    {
        $record['context'] = $this->redactor->redactArray($record['context']);
        $record['message'] = $this->redactor->redactString($record['message']);

        return $record;
    }
}
