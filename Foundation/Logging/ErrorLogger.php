<?php

declare(strict_types=1);

namespace Avax\Logging;

use Carbon\Carbon;
use JsonException;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;
use Throwable;

/**
 * ✅ Class ErrorLogger
 *
 * Implements PSR-3 LoggerInterface for standardized logging.
 * This logger formats and stores log messages using a LogWriterInterface.
 *
 * 🛠 Key Features:
 * - Structured JSON logging for improved readability
 * - Automatic exception handling with full stack traces
 * - Uses PSR-3 log levels with strict validation
 * - Ensures all logs are properly formatted and easy to debug
 */
final readonly class ErrorLogger implements LoggerInterface
{
    /**
     * 🚀 Initializes the logger with a LogWriterInterface instance.
     *
     * @param LogWriterInterface $logWriter The log writer responsible for persisting log messages.
     */
    public function __construct(private LogWriterInterface $logWriter) {}

    // ✅ All standard PSR-3 log levels, mapped to the central logging function

    /**
     * 🚨 Logs an EMERGENCY-level message.
     * 🛑 Used for **critical system failures** where the application is unusable.
     *
     * 🔥 **Example Scenarios:**
     * - Database corruption.
     * - **System-wide failures** that require **immediate** action.
     * - Security breaches (e.g., private data leaks).
     * - Server crashes or complete service downtime.
     *
     * ✅ **Best Practices:**
     * - **Triggers immediate alerts** (e.g., SMS, email, monitoring tools).
     * - **Used sparingly**—this is the **highest severity level**.
     *
     * @param Stringable|string    $message The emergency message.
     * @param array<string, mixed> $context Additional context for debugging.
     */
    public function emergency(Stringable|string $message, array $context = []) : void
    {
        $this->callLogMethod(level: LogLevel::EMERGENCY, message: $message, context: $context);
    }

    /**
     * 🛠 Central method for logging messages at different levels.
     * ✅ Reduces code duplication by handling all log levels in a single function.
     *
     * @param string               $level   The PSR-3 log level.
     * @param Stringable|string    $message The log message.
     * @param array<string, mixed> $context Additional log context.
     */
    private function callLogMethod(string $level, Stringable|string $message, array $context = []) : void
    {
        $this->log(level: $level, message: $message, context: $context);
    }

    /**
     * ✅ Main logging function that:
     * - Validates the log level
     * - Formats the log message with timestamp
     * - Converts exceptions to structured JSON
     * - Writes the log entry using LogWriterInterface
     *
     * @param mixed                $level   The severity level (e.g., LogLevel::ERROR).
     * @param Stringable|string    $message The log message.
     * @param array<string, mixed> $context Additional context for debugging.
     *
     * @throws InvalidArgumentException If the log level is invalid.
     */
    public function log(mixed $level, Stringable|string $message, array $context = []) : void
    {
        // 🔍 Validate log level before proceeding
        if (! $this->isValidLogLevel(level: $level)) {
            throw new InvalidArgumentException(message: "❌ Invalid log level: {$level}");
        }

        // 📝 Format the log entry with Belgrade timezone
        $formattedMessage = sprintf(
            "[%s] %s %s %s\n",
            Carbon::now()->setTimezone(timeZone: 'Europe/Belgrade')->format(format: 'Y-m-d H:i:s'),
            $this->getLogPrefix(level: $level),
            (string) $message,
            $this->formatContext(context: $context)
        );

        // 📡 Write the log entry to the designated log writer
        $this->logWriter->write(content: $formattedMessage);
    }


    /**
     * 🔍 Validates if the provided log level is a valid PSR-3 level.
     */
    private function isValidLogLevel(mixed $level) : bool
    {
        return is_string(value: $level)
            && in_array(
                needle  : $level,
                haystack: [
                    LogLevel::EMERGENCY,
                    LogLevel::ALERT,
                    LogLevel::CRITICAL,
                    LogLevel::ERROR,
                    LogLevel::WARNING,
                    LogLevel::NOTICE,
                    LogLevel::INFO,
                    LogLevel::DEBUG,
                ],
                strict  : true
            );
    }

    /**
     * 🔥 Provides an emoji-based prefix for log levels.
     * ✅ Improves readability in logs.
     */
    private function getLogPrefix(string $level) : string
    {
        return match ($level) {
            LogLevel::EMERGENCY => "🚨 [EMERGENCY]",
            LogLevel::ALERT     => "🚨 [ALERT]",
            LogLevel::CRITICAL  => "🔥 [CRITICAL]",
            LogLevel::ERROR     => "❌ [ERROR]",
            LogLevel::WARNING   => "⚠️ [WARNING]",
            LogLevel::NOTICE    => "ℹ️ [NOTICE]",
            LogLevel::INFO      => "✅ [INFO]",
            LogLevel::DEBUG     => "🐞 [DEBUG]",
            default             => "[LOG]",
        };
    }

    /**
     * 📌 Converts log context to structured JSON.
     * - Handles exceptions and extracts full details.
     * - Uses `JSON_PRETTY_PRINT` for improved log readability.
     *
     * @param array<string, mixed> $context
     *
     * @return string JSON encoded context string or fallback JSON on failure.
     */
    private function formatContext(array $context) : string
    {
        // ✅ Extract full exception details if present
        if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
            $exception = $context['exception'];

            $context['exception'] = [
                'message'  => $exception->getMessage(),
                'file'     => $exception->getFile(),
                'line'     => $exception->getLine(),
                'trace'    => explode(separator: "\n", string: $exception->getTraceAsString()), // Stack trace formatted as an array
                'code'     => $exception->getPrevious() ? $exception->getPrevious()->getCode() : $exception->getCode(),
                'previous' => $exception->getPrevious() ? [
                    'message' => $exception->getPrevious()->getMessage(),
                    'file'    => $exception->getPrevious()->getFile(),
                    'line'    => $exception->getPrevious()->getLine(),
                ] : null,
            ];
        }

        try {
            return json_encode(value: $context, flags: JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (JsonException $e) {
            return json_encode(value: ['error' => 'Context encoding failed', 'message' => $e->getMessage()]);
        }
    }

    /**
     * 🚨 Logs an ALERT-level message.
     * 🔥 Used for situations that require immediate attention.
     *
     * Example: Database connection failures, critical security breaches.
     *
     * @param Stringable|string    $message The alert message.
     * @param array<string, mixed> $context Additional context for debugging.
     */
    public function alert(Stringable|string $message, array $context = []) : void
    {
        $this->callLogMethod(level: LogLevel::ALERT, message: $message, context: $context);
    }

    /**
     * 🔥 Logs a CRITICAL-level message.
     * ✅ Indicates a severe problem that requires immediate intervention.
     *
     * Example: Application component failure, major errors preventing execution.
     *
     * @param Stringable|string    $message The critical message.
     * @param array<string, mixed> $context Additional context for debugging.
     */
    public function critical(Stringable|string $message, array $context = []) : void
    {
        $this->callLogMethod(level: LogLevel::CRITICAL, message: $message, context: $context);
    }

    /**
     * ❌ Logs an ERROR-level message.
     * ⚠️ Used for runtime errors that must be logged and monitored.
     *
     * Example: Exception thrown in production, failed API requests.
     *
     * @param Stringable|string    $message The error message.
     * @param array<string, mixed> $context Additional context for debugging.
     */
    public function error(Stringable|string $message, array $context = []) : void
    {
        $this->callLogMethod(level: LogLevel::ERROR, message: $message, context: $context);
    }

    /**
     * ⚠️ Logs a WARNING-level message.
     * 🔍 Used for potential issues that should be investigated but are not yet critical.
     *
     * Example: Deprecation warnings, retries on failed operations.
     *
     * @param Stringable|string    $message The warning message.
     * @param array<string, mixed> $context Additional context for debugging.
     */
    public function warning(Stringable|string $message, array $context = []) : void
    {
        $this->callLogMethod(level: LogLevel::WARNING, message: $message, context: $context);
    }

    /**
     * ℹ️ Logs a NOTICE-level message.
     * ✅ Represents normal but significant application events.
     *
     * Example: User authentication success, feature usage tracking.
     *
     * @param Stringable|string    $message The notice message.
     * @param array<string, mixed> $context Additional context for debugging.
     */
    public function notice(Stringable|string $message, array $context = []) : void
    {
        $this->callLogMethod(level: LogLevel::NOTICE, message: $message, context: $context);
    }

    /**
     * ✅ Logs an INFO-level message.
     * 📌 Used for informational messages about system state and expected operations.
     *
     * Example: System startup, cron job execution, API call success.
     *
     * @param Stringable|string    $message The info message.
     * @param array<string, mixed> $context Additional context for debugging.
     */
    public function info(Stringable|string $message, array $context = []) : void
    {
        $this->callLogMethod(level: LogLevel::INFO, message: $message, context: $context);
    }

    /**
     * 🐞 Logs a DEBUG-level message.
     * 🛠 Used for detailed debugging information during development.
     *
     * Example: Variable dumps, performance metrics, internal function calls.
     *
     * @param Stringable|string    $message The debug message.
     * @param array<string, mixed> $context Additional context for debugging.
     */
    public function debug(Stringable|string $message, array $context = []) : void
    {
        $this->callLogMethod(level: LogLevel::DEBUG, message: $message, context: $context);
    }

}
