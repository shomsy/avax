<?php

declare(strict_types=1);
namespace Avax\Container\Features\Operate\Boot;

use Throwable;

/**
 * Error handler interface for container bootstrap error management.
 *
 * This interface defines the contract for handling errors and exceptions
 * that occur during container initialization and operation. Implementations
 * can log errors, send notifications, or perform recovery actions.
 *
 * ARCHITECTURAL ROLE:
 * - Defines error handling contract for container lifecycle
 * - Enables pluggable error handling strategies
 * - Supports error logging, reporting, and recovery
 * - Integrates with container bootstrap process
 *
 * ERROR HANDLING SCENARIOS:
 * - Configuration loading failures
 * - Service registration errors
 * - Dependency resolution failures
 * - Runtime operation exceptions
 *
 * USAGE SCENARIOS:
 * ```php
 * $handler = new LoggingErrorHandler($logger);
 * $bootstrapper->setErrorHandler($handler);
 * ```
 *
 * ERROR CATEGORIES:
 * - Bootstrap errors (configuration, initialization)
 * - Runtime errors (service resolution, injection)
 * - Recovery errors (fallback mechanisms)
 * - Diagnostic errors (validation, monitoring)
 *
 * ERROR CONTEXT:
 * - Error type and severity level
 * - Source component and operation
 * - Stack trace and diagnostic information
 * - Timestamp and execution context
 *
 * HANDLING STRATEGIES:
 * - Logging: Record errors for later analysis
 * - Notification: Alert administrators of critical issues
 * - Recovery: Attempt automatic error recovery
 * - Termination: Graceful shutdown on fatal errors
 *
 * LOGGING INTEGRATION:
 * - Compatible with PSR-3 LoggerInterface
 * - Structured logging with context
 * - Error categorization and filtering
 * - Log aggregation and analysis
 *
 * MONITORING INTEGRATION:
 * - Error metrics collection
 * - Alert generation for critical errors
 * - Dashboard integration for error tracking
 * - Trend analysis for error patterns
 *
 * PERFORMANCE IMPACT:
 * - Minimal overhead for error-free operations
 * - Configurable error handling verbosity
 * - Asynchronous error reporting options
 * - Error caching to prevent duplicate handling
 *
 * THREAD SAFETY:
 * - Thread-safe error handling implementations
 * - Atomic error state updates
 * - Concurrent error processing support
 *
 * @package Avax\Container\Operate\Boot
 * @see docs/Features/Operate/Boot/ErrorHandlerInterface.md#quick-summary
 */
interface ErrorHandlerInterface
{
    /**
     * Handle an error or exception during container operation.
     *
     * Processes errors and exceptions that occur during container lifecycle.
     * The handler can log the error, attempt recovery, or escalate as needed.
     *
     * ERROR CONTEXT:
     * The exception parameter contains the error that occurred, including:
     * - Exception type and message
     * - Stack trace for debugging
     * - Error code and additional context
     * - Nested exceptions for error chains
     *
     * HANDLING DECISIONS:
     * - Log the error for later analysis
     * - Attempt automatic recovery if possible
     * - Escalate critical errors to administrators
     * - Continue operation or trigger shutdown
     *
     * RECOVERY STRATEGIES:
     * - Retry failed operations with backoff
     * - Use fallback configurations or services
     * - Degrade gracefully to reduced functionality
     * - Reinitialize failed components
     *
     * ERROR ESCALATION:
     * - Critical errors may trigger system alerts
     * - Performance-impacting errors may adjust behavior
     * - Security-related errors may activate safeguards
     * - Persistent errors may initiate system shutdown
     *
     * CONTEXT INFORMATION:
     * The context parameter provides additional information:
     * - Current operation being performed
     * - Component that generated the error
     * - User context and session information
     * - System state at time of error
     *
     * @param \Throwable  $exception The error or exception that occurred
     * @param object|null $context   Additional context about the error (usually the container instance)
     *
     * @return void
     * @see docs/Features/Operate/Boot/ErrorHandlerInterface.md#method-handleerror
     */
    public function handleError(Throwable $exception, object|null $context = null) : void;
}
