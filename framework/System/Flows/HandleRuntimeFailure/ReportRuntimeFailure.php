<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleRuntimeFailure;

use Avax\Components\Operations\Logging\System\PublicSurface\Logging;
use Throwable;
use function Sentry\captureException;

/**
 * Reports runtime failures to the logging system and external error trackers.
 *
 * Enriches error reports with request context, correlation IDs, and user information
 * when available.
 */
final readonly class ReportRuntimeFailure
{
    public function __construct(
        private Logging|null $logger = null,
        private string|null  $correlationId = null,
        private string|null  $traceId = null,
    ) {}

    /**
     * Report a runtime failure with full context.
     */
    public function report(Throwable $throwable) : void
    {
        $context = $this->buildContext($throwable);

        if ($this->logger !== null) {
            $this->logger->critical(
                message: $this->buildLogMessage($throwable),
                context: $context,
            );
        }

        $this->reportToExternalTrackers($throwable, $context);
    }

    /**
     * Build the context array for error reporting.
     *
     * @return array<string, mixed>
     */
    private function buildContext(Throwable $throwable) : array
    {
        $context = [
            'exception_class'   => $throwable::class,
            'exception_message' => $throwable->getMessage(),
            'exception_file'    => $throwable->getFile(),
            'exception_line'    => $throwable->getLine(),
            'exception_code'    => $throwable->getCode(),
            'trace'             => $this->formatTrace($throwable),
        ];

        if ($this->correlationId !== null) {
            $context['correlation_id'] = $this->correlationId;
        }

        if ($this->traceId !== null) {
            $context['trace_id'] = $this->traceId;
        }

        $requestInfo = $this->getRequestInfo();
        if ($requestInfo !== []) {
            $context['request'] = $requestInfo;
        }

        $userInfo = $this->getUserInfo();
        if ($userInfo !== []) {
            $context['user'] = $userInfo;
        }

        return $context;
    }

    /**
     * Format the exception trace for logging.
     *
     * @return list<array{file: string, line: int, class: string|null, type: string|null, function: string}>
     */
    private function formatTrace(Throwable $throwable) : array
    {
        $trace = [];

        foreach ($throwable->getTrace() as $frame) {
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
            $info['ip'] = $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $info['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }

        return $info;
    }

    /**
     * Extract user information from the global scope.
     *
     * @return array<string, mixed>
     */
    private function getUserInfo() : array
    {
        $info = [];

        if (isset($_SESSION['user_id'])) {
            $info['id'] = $_SESSION['user_id'];
        }

        if (isset($_SESSION['user_email'])) {
            $info['email'] = $_SESSION['user_email'];
        }

        return $info;
    }

    /**
     * Build a human-readable log message.
     */
    private function buildLogMessage(Throwable $throwable) : string
    {
        $message = sprintf(
            'Runtime failure: %s in %s:%d',
            $throwable::class,
            $throwable->getFile(),
            $throwable->getLine(),
        );

        if ($this->correlationId !== null) {
            $message .= " [correlation_id: {$this->correlationId}]";
        }

        return $message;
    }

    /**
     * Report to external error tracking services (e.g., Sentry).
     *
     * @param array<string, mixed> $context
     */
    private function reportToExternalTrackers(Throwable $throwable, array $context) : void
    {
        // Integration point for external error trackers like Sentry.
        // Example:
        // if (function_exists('Sentry\captureException')) {
        //     \Sentry\captureException($throwable);
        // }

        if (function_exists('Sentry\captureException')) {
            captureException($throwable);
        }
    }
}
