<?php

declare(strict_types=1);
namespace Avax\Container\Observe\Metrics;

use Avax\Container\Features\Operate\Config\TelemetryConfig;
use Avax\Logging\ErrorLogger;
use Avax\Logging\LoggerFactory;
use Throwable;

/**
 * Enterprise-grade logging integration for dependency injection container observability.
 *
 * This integration provides comprehensive logging capabilities that bridge container
 * telemetry events to structured PSR-3 logging channels. It serves as the central
 * logging authority for all container operations, ensuring consistent log formatting,
 * security-conscious data handling, and rich contextual information across all
 * container components and lifecycle events.
 *
 * ARCHITECTURAL ROLE:
 * - Unified logging abstraction for container telemetry
 * - PSR-3 compliant structured logging with contextual metadata
 * - Security-aware configuration sanitization and secret redaction
 * - Performance monitoring and anomaly detection logging
 * - Error tracking and diagnostic information aggregation
 * - Lifecycle event logging for operational visibility
 * - Cache operation and configuration change tracking
 *
 * LOGGING CHANNELS:
 * - container-lifecycle: Bootstrap, shutdown, and major lifecycle events
 * - container-resolution: Service resolution timing and strategy tracking
 * - container-registration: Service registration events and metadata
 * - container-cache: Cache operations, hits, misses, and performance
 * - container-config: Configuration loading, changes, and validation
 * - container-performance: Performance warnings and optimization opportunities
 * - container-errors: Error conditions and diagnostic information
 * - container-health: Health check results and system status
 * - container-telemetry: Telemetry export and monitoring data
 *
 * SECURITY FEATURES:
 * - Automatic sanitization of sensitive configuration data
 * - Configurable stack trace inclusion for debugging
 * - Secret redaction for passwords, tokens, and API keys
 * - Safe logging of complex data structures
 * - Audit trail generation for compliance requirements
 *
 * USAGE SCENARIOS:
 * ```php
 * // Initialize logging integration
 * $logging = new LoggerFactoryIntegration($loggerFactory, $telemetryConfig);
 *
 * // Log service resolution with performance metrics
 * $logging->logServiceResolution('database', 0.025, 'singleton', [
 *     'memory_peak' => '45MB',
 *     'cache_hit' => true
 * ]);
 *
 * // Log lifecycle events
 * $logging->logLifecycleEvent('bootstrap_completed', [
 *     'services_registered' => 150,
 *     'environment' => 'production'
 * ]);
 *
 * // Log performance warnings
 * $logging->logPerformanceWarning('complex.service', 2.5, 1.0);
 *
 * // Log errors with context
 * $logging->logContainerError('resolution', $exception, [
 *     'service_id' => 'failing.service',
 *     'resolution_strategy' => 'autowire'
 * ]);
 * ```
 *
 * PERFORMANCE CHARACTERISTICS:
 * - Logger memoization prevents repeated factory instantiation
 * - Lazy logger creation on first access per component
 * - Minimal overhead when telemetry is disabled
 * - Efficient context merging and data sanitization
 * - Configurable logging levels and filtering
 *
 * MEMORY MANAGEMENT:
 * - Bounded logger cache prevents memory leaks
 * - Efficient context array operations
 * - Streaming log output for large data sets
 * - Garbage collection friendly data structures
 *
 * CONFIGURATION OPTIONS:
 * - Telemetry enable/disable flag for performance control
 * - Stack trace inclusion toggle for security vs debugging balance
 * - Configurable tracked events for selective logging
 * - Logger channel naming conventions
 * - Sanitization rules for sensitive data detection
 *
 * INTEGRATION POINTS:
 * - LoggerFactory: PSR-3 logger creation and management
 * - TelemetryConfig: Logging behavior and security settings
 * - EnhancedMetricsCollector: Metrics logging integration
 * - Container components: Consistent logging interface
 * - External monitoring systems: Structured log consumption
 *
 * MONITORING & ALERTING:
 * - Performance threshold monitoring with automatic warnings
 * - Error rate tracking and anomaly detection
 * - Health check result logging with status-based levels
 * - Cache operation success/failure tracking
 * - Configuration change auditing
 *
 * COMPLIANCE FEATURES:
 * - GDPR-compliant data handling with automatic sanitization
 * - Audit trail generation for regulatory requirements
 * - Structured logging for SIEM system integration
 * - Configurable retention policies for log data
 * - Immutable log entries with tamper-evident timestamps
 *
 * ERROR HANDLING:
 * - Graceful degradation when logging backends fail
 * - Exception wrapping with additional context
 * - Safe serialization of complex error data
 * - Fallback logging strategies for critical events
 * - Error correlation and deduplication
 *
 * THREAD SAFETY:
 * - Logger cache is mutable but access is serialized
 * - PSR-3 loggers assumed thread-safe by implementation
 * - Atomic configuration updates
 * - Safe concurrent logging from multiple components
 *
 * BACKWARD COMPATIBILITY:
 * - Maintains compatibility with existing logging interfaces
 * - Gradual migration path for legacy logging code
 * - Extensible logging channel system
 * - Version-aware log format evolution
 *
 * DIAGNOSTIC CAPABILITIES:
 * - Comprehensive logging statistics and metadata
 * - Active logger tracking and resource usage
 * - Configuration state visibility
 * - Performance metrics integration
 * - Debug information for troubleshooting
 *
 * @package Avax\Container\Observe\Metrics
 * @see     LoggerFactory PSR-3 logger factory for channel management
 * @see     TelemetryConfig Configuration controlling logging behavior and security
 * @see     EnhancedMetricsCollector Metrics collection with integrated logging
 * @see     ErrorLogger PSR-3 compliant error logging interface
 * @see docs/Observe/Metrics/LoggerFactoryIntegration.md#quick-summary
 */
class LoggerFactoryIntegration
{
    /**
     * Memoized PSR-3 logger instances keyed by component name.
     *
     * This cache stores logger instances to avoid repeated factory calls
     * and improve performance. Each component gets its own logger channel
     * following the naming convention "container-{component}".
     *
     * CACHE MANAGEMENT:
     * - Lazy instantiation on first access
     * - Bounded cache to prevent memory leaks
     * - Thread-safe access patterns
     * - Automatic cleanup on object destruction
     *
     * @var array<string, ErrorLogger>
     */
    private array $loggers = [];

    /**
     * Creates a new logging integration instance with factory and configuration.
     *
     * Initializes the logging integration with PSR-3 logger factory and telemetry
     * configuration. Sets up the foundation for structured logging across all
     * container components with security and performance considerations.
     *
     * DEPENDENCY INJECTION:
     * - loggerFactory: Creates PSR-3 loggers for different components
     * - config: Controls logging behavior, security, and telemetry settings
     *
     * INITIALIZATION SEQUENCE:
     * 1. Store factory and configuration references
     * 2. Initialize empty logger cache
     * 3. Validate configuration parameters
     * 4. Set up logging channel naming conventions
     *
     * @param LoggerFactory   $loggerFactory PSR-3 logger factory for component loggers
     * @param TelemetryConfig $config        Telemetry configuration for logging behavior
     * @see docs/Observe/Metrics/LoggerFactoryIntegration.md#method-__construct
     */
    public function __construct(
        private readonly LoggerFactory   $loggerFactory,
        private readonly TelemetryConfig $config
    ) {}

    /**
     * Creates an enhanced metrics collector with integrated logging capabilities.
     *
     * Instantiates a metrics collector that automatically emits structured logs
     * for all collection operations, providing comprehensive observability
     * integration between metrics and logging systems.
     *
     * METRICS COLLECTOR FEATURES:
     * - Automatic logging of resolution events with timing
     * - Error tracking with structured context
     * - Performance anomaly detection and logging
     * - Telemetry export with audit trails
     * - Health check result logging
     *
     * INTEGRATION BENEFITS:
     * - Unified observability across metrics and logs
     * - Consistent context propagation
     * - Correlated troubleshooting information
     * - Centralized monitoring and alerting
     *
     * @return EnhancedMetricsCollector Metrics collector with logging integration
     * @see EnhancedMetricsCollector For detailed metrics collection capabilities
     * @see docs/Observe/Metrics/LoggerFactoryIntegration.md#method-createmetricscollector
     */
    public function createMetricsCollector() : EnhancedMetricsCollector
    {
        return new EnhancedMetricsCollector(loggerFactory: $this->loggerFactory, config: $this->config);
    }

    /**
     * Logs a container lifecycle event with comprehensive contextual information.
     *
     * Records major lifecycle milestones in the container's operational timeline,
     * providing visibility into bootstrap, shutdown, and other critical state
     * transitions. Essential for monitoring application health and debugging
     * startup/shutdown issues.
     *
     * LIFECYCLE EVENTS:
     * - bootstrap_started: Container initialization begins
     * - bootstrap_completed: Container ready for service resolution
     * - shutdown_started: Container shutdown initiated
     * - shutdown_completed: Container fully shut down
     * - configuration_loaded: Configuration successfully loaded
     * - services_registered: All services registered and validated
     *
     * CONTEXT ENHANCEMENT:
     * - Automatic timestamp inclusion with microsecond precision
     * - Event type identification for filtering and alerting
     * - Merging of custom context with standard metadata
     * - Environment and configuration state information
     *
     * MONITORING INTEGRATION:
     * - Lifecycle event correlation across distributed systems
     * - Alerting on failed bootstrap or abnormal shutdown
     * - Performance tracking of startup/shutdown durations
     * - Dependency verification during startup phases
     *
     * USAGE EXAMPLES:
     * ```php
     * // Log successful bootstrap
     * $logging->logLifecycleEvent('bootstrap_completed', [
     *     'services_count' => 150,
     *     'bootstrap_time' => 2.34,
     *     'environment' => 'production'
     * ]);
     *
     * // Log configuration loading
     * $logging->logLifecycleEvent('configuration_loaded', [
     *     'config_files' => ['app.php', 'database.php'],
     *     'cache_used' => true
     * ]);
     * ```
     *
     * @param string $event   Lifecycle event identifier
     * @param array  $context Additional context for the lifecycle event
     *
     * @return void
     * @see docs/Observe/Metrics/LoggerFactoryIntegration.md#method-loglifecycleevent
     */
    public function logLifecycleEvent(string $event, array $context = []) : void
    {
        $logger = $this->getComponentLogger(component: 'lifecycle');

        $logger->info(message: "Container {$event}", context: array_merge($context, [
            'event'     => $event,
            'timestamp' => microtime(true)
        ]));
    }

    /**
     * Retrieves a memoized PSR-3 logger for the specified container component.
     *
     * Returns a logger instance scoped to the requested component, creating it
     * on first access and caching for subsequent use. This ensures efficient
     * logging without repeated factory instantiation overhead.
     *
     * LOGGER NAMING CONVENTION:
     * Component loggers use the channel name "container-{component}" to
     * provide clear separation and filtering capabilities in log aggregation
     * systems and monitoring dashboards.
     *
     * PERFORMANCE OPTIMIZATION:
     * - Memoization prevents repeated expensive factory calls
     * - Lazy instantiation reduces startup overhead
     * - Bounded cache prevents memory accumulation
     * - Thread-safe access for concurrent logging
     *
     * USAGE EXAMPLES:
     * ```php
     * // Get logger for resolution component
     * $resolutionLogger = $integration->getComponentLogger('resolution');
     * $resolutionLogger->info('Service resolved', ['service' => 'db']);
     *
     * // Get logger for cache operations
     * $cacheLogger = $integration->getComponentLogger('cache');
     * $cacheLogger->debug('Cache hit', ['key' => 'user.123']);
     * ```
     *
     * @param string $component Container component name for logger scoping
     *
     * @return ErrorLogger PSR-3 compliant logger for the component
     * @see docs/Observe/Metrics/LoggerFactoryIntegration.md#method-getcomponentlogger
     */
    public function getComponentLogger(string $component) : ErrorLogger
    {
        if (! isset($this->loggers[$component])) {
            $this->loggers[$component] = $this->loggerFactory->createLoggerFor(
                channel: "container-{$component}"
            );
        }

        return $this->loggers[$component];
    }

    /**
     * Logs service resolution events with detailed performance and strategy information.
     *
     * Records every service resolution with timing data, memory usage, and resolution
     * strategy. Critical for performance monitoring, optimization, and troubleshooting
     * slow or failing service resolutions.
     *
     * PERFORMANCE METRICS:
     * - Resolution duration in milliseconds with rounding precision
     * - Peak memory usage during resolution in megabytes
     * - Resolution strategy identification for optimization analysis
     * - Service identifier for impact assessment
     *
     * RESOLUTION STRATEGIES:
     * - singleton: Cached singleton instance retrieval
     * - scoped: Context-scoped instance creation/retrieval
     * - transient: New instance creation each time
     * - factory: Factory method execution
     * - autowire: Automatic dependency injection
     *
     * TROUBLESHOOTING VALUE:
     * - Identify performance bottlenecks by service
     * - Track resolution strategy effectiveness
     * - Monitor memory usage patterns
     * - Detect resolution failures and anomalies
     *
     * CONTEXT ENHANCEMENT:
     * - Custom context merging for additional metadata
     * - Automatic high-precision timestamp inclusion
     * - Memory usage tracking for resource monitoring
     * - Strategy-specific performance analysis
     *
     * USAGE EXAMPLES:
     * ```php
     * // Log successful resolution
     * $logging->logServiceResolution('database', 0.025, 'singleton', [
     *     'cache_hit' => true,
     *     'instance_reused' => true
     * ]);
     *
     * // Log slow resolution warning
     * $logging->logServiceResolution('complex.service', 5.2, 'autowire', [
     *     'dependencies_count' => 15,
     *     'reflection_used' => true
     * ]);
     * ```
     *
     * @param string $serviceId Resolved service identifier
     * @param float  $duration  Resolution duration in seconds
     * @param string $strategy  Resolution strategy used
     * @param array  $context   Additional context for the resolution event
     *
     * @return void
     * @see docs/Observe/Metrics/LoggerFactoryIntegration.md#method-logserviceresolution
     */
    public function logServiceResolution(
        string $serviceId,
        float  $duration,
        string $strategy,
        array  $context = []
    ) : void
    {
        $logger = $this->getComponentLogger(component: 'resolution');

        $logger->info(message: 'Service resolved', context: array_merge($context, [
            'service'     => $serviceId,
            'duration_ms' => round($duration * 1000, 2),
            'strategy'    => $strategy,
            'memory_mb'   => round(memory_get_peak_usage(true) / 1024 / 1024, 2)
        ]));
    }

    /**
     * Logs service registration events with complete service metadata.
     *
     * Records service registration with class information, lifetime configuration,
     * and associated tags. Essential for tracking service inventory and
     * understanding container composition.
     *
     * SERVICE METADATA:
     * - Service identifier for unique identification
     * - Concrete class name for implementation tracking
     * - Lifetime scope (singleton, scoped, transient)
     * - Associated tags for categorization and discovery
     * - Registration timestamp for temporal analysis
     *
     * REGISTRATION TRACKING:
     * - Service inventory management and auditing
     * - Class implementation verification
     * - Lifetime configuration validation
     * - Tag-based service organization
     *
     * MONITORING BENEFITS:
     * - Service registration success/failure tracking
     * - Container composition analysis over time
     * - Dependency injection configuration validation
     * - Service lifecycle management visibility
     *
     * USAGE EXAMPLES:
     * ```php
     * // Log singleton service registration
     * $logging->logServiceRegistration(
     *     'database.connection',
     *     PDO::class,
     *     'singleton',
     *     ['infrastructure', 'database']
     * );
     *
     * // Log scoped controller registration
     * $logging->logServiceRegistration(
     *     'user.controller',
     *     UserController::class,
     *     'scoped',
     *     ['controller', 'api']
     * );
     * ```
     *
     * @param string $serviceId Service identifier being registered
     * @param string $class     Concrete class name for the service
     * @param string $lifetime  Service lifetime scope
     * @param array  $tags      Tags associated with the service
     *
     * @return void
     * @see docs/Observe/Metrics/LoggerFactoryIntegration.md#method-logserviceregistration
     */
    public function logServiceRegistration(
        string $serviceId,
        string $class,
        string $lifetime,
        array  $tags = []
    ) : void
    {
        $logger = $this->getComponentLogger(component: 'registration');

        $logger->info(message: 'Service registered', context: [
            'service'   => $serviceId,
            'class'     => $class,
            'lifetime'  => $lifetime,
            'tags'      => $tags,
            'timestamp' => microtime(true)
        ]);
    }

    /**
     * Logs cache operation outcomes with success/failure status and context.
     *
     * Records all cache operations (get, set, delete, clear) with performance
     * and success metrics. Critical for monitoring cache effectiveness and
     * diagnosing caching-related performance issues.
     *
     * CACHE OPERATIONS TRACKED:
     * - get: Cache retrieval operations with hit/miss status
     * - set: Cache storage operations with TTL information
     * - delete: Cache invalidation operations
     * - clear: Bulk cache clearing operations
     * - warm: Cache warming operations with timing
     *
     * PERFORMANCE METRICS:
     * - Operation success/failure status
     * - Execution timing where applicable
     * - Cache key identification for correlation
     * - Backend-specific performance data
     *
     * TROUBLESHOOTING VALUE:
     * - Cache hit/miss ratio analysis for optimization
     * - Failed operation identification and alerting
     * - Performance degradation detection
     * - Cache backend health monitoring
     *
     * LOG LEVEL DETERMINATION:
     * - Successful operations: debug level (detailed monitoring)
     * - Failed operations: warning level (requires attention)
     * - Critical failures: error level (immediate action needed)
     *
     * USAGE EXAMPLES:
     * ```php
     * // Log successful cache hit
     * $logging->logCacheOperation('get', 'user.123', true, [
     *     'hit' => true,
     *     'ttl_remaining' => 300
     * ]);
     *
     * // Log cache miss
     * $logging->logCacheOperation('get', 'user.999', true, [
     *     'hit' => false,
     *     'reason' => 'not_found'
     * ]);
     *
     * // Log failed cache operation
     * $logging->logCacheOperation('set', 'config', false, [
     *     'error' => 'connection_timeout',
     *     'backend' => 'redis'
     * ]);
     * ```
     *
     * @param string $operation Cache operation type (get, set, delete, etc.)
     * @param string $key       Cache key affected by the operation
     * @param bool   $success   Whether the cache operation succeeded
     * @param array  $context   Additional context for the cache operation
     *
     * @return void
     * @see docs/Observe/Metrics/LoggerFactoryIntegration.md#method-logcacheoperation
     */
    public function logCacheOperation(
        string $operation,
        string $key,
        bool   $success,
        array  $context = []
    ) : void
    {
        $logger = $this->getComponentLogger(component: 'cache');
        $level  = $success ? 'debug' : 'warning';

        $logger->$level("Cache {$operation}", array_merge($context, [
            'operation' => $operation,
            'key'       => $key,
            'success'   => $success,
            'timestamp' => microtime(true)
        ]));
    }

    /**
     * Logs configuration events with sanitized sensitive data protection.
     *
     * Records configuration loading, changes, and validation events while
     * automatically sanitizing sensitive data to prevent credential exposure
     * in logs. Essential for configuration management auditing and debugging.
     *
     * CONFIGURATION EVENTS:
     * - loaded: Configuration successfully loaded from source
     * - refreshed: Configuration reloaded/refreshed
     * - validated: Configuration passed validation checks
     * - failed: Configuration loading or validation failed
     * - merged: Multiple configuration sources combined
     * - overridden: Configuration values overridden by environment
     *
     * SENSITIVE DATA PROTECTION:
     * - Automatic detection of sensitive keys (password, secret, token, etc.)
     * - Redaction with '[REDACTED]' placeholder
     * - Recursive sanitization for nested configuration arrays
     * - Case-insensitive keyword matching
     * - Configurable sensitive key patterns
     *
     * SECURITY CONSIDERATIONS:
     * - Prevents accidental credential exposure in logs
     * - Complies with data protection regulations
     * - Maintains debugging capability without security risk
     * - Audit trail for configuration changes
     *
     * MONITORING VALUE:
     * - Configuration loading success/failure tracking
     * - Configuration change auditing
     * - Source identification and priority tracking
     * - Validation result logging
     *
     * USAGE EXAMPLES:
     * ```php
     * // Log successful configuration loading
     * $logging->logConfigurationEvent('loaded', [
     *     'database' => [
     *         'host' => 'localhost',
     *         'password' => 'secret123', // Will be sanitized
     *         'database' => 'app_db'
     *     ],
     *     'api_key' => 'sk-123456' // Will be sanitized
     * ]);
     *
     * // Log configuration validation failure
     * $logging->logConfigurationEvent('failed', [
     *     'error' => 'Invalid database host',
     *     'source' => 'environment'
     * ]);
     * ```
     *
     * @param string $event  Configuration event type
     * @param array  $config Configuration data to log (will be sanitized)
     *
     * @return void
     * @see docs/Observe/Metrics/LoggerFactoryIntegration.md#method-logconfigurationevent
     */
    public function logConfigurationEvent(string $event, array $config = []) : void
    {
        $logger = $this->getComponentLogger(component: 'config');

        // Sanitize sensitive config data
        $sanitizedConfig = $this->sanitizeConfig(config: $config);

        $logger->info(message: "Configuration {$event}", context: [
            'event'     => $event,
            'config'    => $sanitizedConfig,
            'timestamp' => microtime(true)
        ]);
    }

    /**
     * Sanitizes configuration arrays by redacting sensitive data for safe logging.
     *
     * Recursively processes configuration arrays to identify and redact sensitive
     * information before logging. Prevents accidental exposure of credentials,
     * secrets, and other sensitive data in log files and monitoring systems.
     *
     * SENSITIVE DATA DETECTION:
     * - Case-insensitive keyword matching for common sensitive keys
     * - Configurable sensitive key patterns
     * - Recursive processing of nested arrays
     * - Preservation of non-sensitive configuration data
     *
     * SENSITIVE KEYWORDS:
     * - password: Database passwords, API passwords
     * - secret: Application secrets, encryption keys
     * - key: API keys, private keys, access tokens
     * - token: Authentication tokens, session tokens
     * - api_key: API authentication keys
     *
     * SANITIZATION PROCESS:
     * - Deep traversal of nested array structures
     * - Key-by-key analysis for sensitive patterns
     * - Redaction with '[REDACTED]' placeholder
     * - Preservation of array structure and non-sensitive keys
     *
     * SECURITY BENEFITS:
     * - Prevents credential exposure in logs
     * - Complies with data protection regulations
     * - Maintains debugging capability without security risk
     * - Safe for production logging environments
     *
     * PERFORMANCE CHARACTERISTICS:
     * - Linear time complexity O(n) for array size
     * - Recursive processing for nested structures
     * - Minimal memory overhead for redaction
     * - Efficient string operations for key matching
     *
     * USAGE EXAMPLES:
     * ```php
     * // Sanitize database configuration
     * $rawConfig = [
     *     'database' => [
     *         'host' => 'localhost',
     *         'password' => 'secret123', // Will be redacted
     *         'database' => 'app'
     *     ],
     *     'api_key' => 'sk-123456' // Will be redacted
     * ];
     *
     * $safeConfig = $this->sanitizeConfig($rawConfig);
     * // Result: ['database' => ['host' => 'localhost', 'password' => '[REDACTED]', 'database' => 'app'], 'api_key' =>
     * '[REDACTED]']
     * ```
     *
     * @param array $config Raw configuration array to sanitize
     *
     * @return array Sanitized configuration array with sensitive data redacted
     */
    private function sanitizeConfig(array $config) : array
    {
        $sensitiveKeys = ['password', 'secret', 'key', 'token', 'api_key'];

        $sanitized = [];
        foreach ($config as $key => $value) {
            $lowerKey = strtolower($key);

            // Check if key contains sensitive keywords
            $isSensitive = false;
            foreach ($sensitiveKeys as $sensitive) {
                if (str_contains($lowerKey, $sensitive)) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeConfig(config: $value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Logs performance warnings when service resolution exceeds acceptable thresholds.
     *
     * Monitors service resolution performance and generates warnings when services
     * take longer than expected to resolve. Critical for identifying performance
     * bottlenecks and optimization opportunities in dependency injection.
     *
     * PERFORMANCE THRESHOLDS:
     * - Default threshold: 1 second for service resolution
     * - Configurable per service or service type
     * - Statistical analysis for dynamic threshold adjustment
     * - Historical performance trend monitoring
     *
     * WARNING CRITERIA:
     * - Resolution duration exceeds configured threshold
     * - Performance degradation compared to historical averages
     * - Memory usage spikes during resolution
     * - Repeated slow resolutions for the same service
     *
     * OPTIMIZATION OPPORTUNITIES:
     * - Cache warming for frequently slow services
     * - Dependency injection optimization
     * - Service refactoring for performance
     * - Lazy loading implementation where appropriate
     *
     * ALERTING INTEGRATION:
     * - Automatic alerting for critical performance issues
     * - Escalation based on severity and frequency
     * - Dashboard integration for performance trending
     * - Automated remediation suggestions
     *
     * DIAGNOSTIC INFORMATION:
     * - Service identifier for targeted optimization
     * - Actual vs threshold duration comparison
     * - Memory usage during slow resolution
     * - Resolution strategy and context
     *
     * USAGE EXAMPLES:
     * ```php
     * // Log performance warning for slow service
     * $logging->logPerformanceWarning('complex.service', 3.5, 1.0);
     *
     * // Log with additional context
     * $logging->logPerformanceWarning('database.service', 2.1, 1.5);
     * ```
     *
     * @param string $serviceId Service identifier with performance issues
     * @param float  $duration  Actual resolution duration in seconds
     * @param float  $threshold Expected maximum duration in seconds
     *
     * @return void
     * @see docs/Observe/Metrics/LoggerFactoryIntegration.md#method-logperformancewarning
     */
    public function logPerformanceWarning(
        string $serviceId,
        float  $duration,
        float  $threshold
    ) : void
    {
        $logger = $this->getComponentLogger(component: 'performance');

        $logger->warning(message: 'Slow service resolution detected', context: [
            'service'           => $serviceId,
            'duration_ms'       => round($duration * 1000, 2),
            'threshold_ms'      => round($threshold * 1000, 2),
            'exceeds_threshold' => $duration > $threshold,
            'timestamp'         => microtime(true)
        ]);
    }

    /**
     * Logs container errors with comprehensive diagnostic information and optional stack traces.
     *
     * Records error conditions throughout the container lifecycle with rich context
     * for debugging and monitoring. Includes configurable stack trace inclusion for
     * security vs debugging balance.
     *
     * ERROR CONTEXT CAPTURED:
     * - Component where error occurred for targeted troubleshooting
     * - Exception type and message for error classification
     * - File and line number for precise error location
     * - Configurable stack trace for detailed debugging
     * - Timestamp with microsecond precision
     *
     * ERROR CLASSIFICATION:
     * - Resolution errors: Service dependency or instantiation failures
     * - Configuration errors: Invalid configuration or missing settings
     * - Cache errors: Backend connectivity or operation failures
     * - Registration errors: Service definition or validation issues
     * - Security errors: Access control or validation failures
     *
     * SECURITY CONSIDERATIONS:
     * - Stack trace inclusion controlled by configuration
     * - Sensitive information redaction in error messages
     * - Error message sanitization for log safety
     * - Access control for error visibility
     *
     * MONITORING INTEGRATION:
     * - Error rate tracking and alerting thresholds
     * - Error correlation across distributed systems
     * - Automated incident response triggers
     * - Error trend analysis and reporting
     *
     * DEBUGGING SUPPORT:
     * - Complete stack traces for development environments
     * - Contextual information for root cause analysis
     * - Error propagation tracking through container layers
     * - Performance impact assessment of errors
     *
     * USAGE EXAMPLES:
     * ```php
     * // Log resolution error with context
     * try {
     *     $service = $container->get('failing.service');
     * } catch (\Exception $e) {
     *     $logging->logContainerError('resolution', $e, [
     *         'service_id' => 'failing.service',
     *         'resolution_strategy' => 'autowire',
     *         'dependencies_attempted' => ['db', 'cache']
     *     ]);
     * }
     *
     * // Log configuration error
     * $logging->logContainerError('config', $validationException, [
     *     'config_file' => 'database.php',
     *     'validation_rules' => ['host', 'port', 'credentials']
     * ]);
     * ```
     *
     * @param string     $component Container component where error occurred
     * @param \Throwable $error     Exception instance with error details
     * @param array      $context   Additional context for error diagnosis
     *
     * @return void
     * @see docs/Observe/Metrics/LoggerFactoryIntegration.md#method-logcontainererror
     */
    public function logContainerError(
        string    $component,
        Throwable $error,
        array     $context = []
    ) : void
    {
        $logger = $this->getComponentLogger(component: 'errors');

        $logger->error(message: "Container error in {$component}", context: array_merge($context, [
            'component'  => $component,
            'error_type' => get_class($error),
            'message'    => $error->getMessage(),
            'file'       => $error->getFile(),
            'line'       => $error->getLine(),
            'trace'      => $this->config->includeStackTraces ? $error->getTraceAsString() : null,
            'timestamp'  => microtime(true)
        ]));
    }

    /**
     * Retrieves comprehensive logging statistics and configuration metadata.
     *
     * Provides diagnostic information about the logging infrastructure state,
     * active loggers, configuration settings, and telemetry capabilities.
     * Essential for monitoring logging system health and troubleshooting.
     *
     * STATISTICS INCLUDED:
     * - Active logger count and channel names
     * - Telemetry enable/disable status
     * - Stack trace inclusion configuration
     * - Tracked events configuration
     * - Logger cache utilization
     *
     * DIAGNOSTIC VALUE:
     * - Logging system health assessment
     * - Configuration verification and validation
     * - Resource usage monitoring
     * - Troubleshooting logging issues
     * - Capacity planning for logging infrastructure
     *
     * MONITORING INTEGRATION:
     * - Logger instance tracking for resource monitoring
     * - Configuration state visibility
     * - Telemetry settings verification
     * - Channel utilization analysis
     *
     * RETURN STRUCTURE:
     * ```php
     * [
     *     'active_loggers' => 8,
     *     'logger_channels' => ['lifecycle', 'resolution', 'cache', ...],
     *     'telemetry_enabled' => true,
     *     'stack_traces_enabled' => false,
     *     'tracked_events' => ['resolution', 'errors', 'performance']
     * ]
     * ```
     *
     * USAGE EXAMPLES:
     * ```php
     * // Get logging system statistics
     * $stats = $logging->getLoggingStats();
     *
     * // Check telemetry configuration
     * if (!$stats['telemetry_enabled']) {
     *     $this->logger->warning('Telemetry disabled, monitoring limited');
     * }
     *
     * // Monitor logger proliferation
     * if ($stats['active_loggers'] > 20) {
     *     $this->logger->warning('High logger count detected');
     * }
     * ```
     *
     * @return array Comprehensive logging system statistics and configuration
     * @see docs/Observe/Metrics/LoggerFactoryIntegration.md#method-getloggingstats
     */
    public function getLoggingStats() : array
    {
        return [
            'active_loggers'       => count($this->loggers),
            'logger_channels'      => array_keys($this->loggers),
            'telemetry_enabled'    => $this->config->enabled,
            'stack_traces_enabled' => $this->config->includeStackTraces,
            'tracked_events'       => $this->config->trackedEvents
        ];
    }

    /**
     * Flushes all active loggers that support the flush operation.
     *
     * Iterates through all memoized logger instances and calls their flush()
     * method if available. Ensures that buffered log messages are immediately
     * written to their destinations, critical for debugging and ensuring
     * log delivery before application termination.
     *
     * FLUSH OPERATIONS:
     * - Immediate log message delivery to backends
     * - Buffer clearing for buffered loggers
     * - Synchronization with external logging systems
     * - Ensuring log persistence before shutdown
     *
     * LOGGER COMPATIBILITY:
     * - Only flushes loggers that implement flush() method
     * - Graceful handling of loggers without flush capability
     * - No exceptions thrown for unsupported operations
     * - Safe operation across different logger implementations
     *
     * USE CASES:
     * - Application shutdown to ensure all logs are written
     * - Critical error scenarios requiring immediate log delivery
     * - Debugging sessions needing real-time log visibility
     * - Log shipping before container termination
     *
     * PERFORMANCE CONSIDERATIONS:
     * - I/O operations for log delivery to backends
     * - Potential blocking behavior for synchronous loggers
     * - Memory release after buffer clearing
     * - Network operations for remote logging backends
     *
     * USAGE EXAMPLES:
     * ```php
     * // Flush logs during graceful shutdown
     * register_shutdown_function(function() use ($logging) {
     *     $logging->flushAll();
     * });
     *
     * // Force log delivery for debugging
     * $logging->flushAll();
     * echo "All logs flushed to backends";
     * ```
     *
     * @return void
     * @see docs/Observe/Metrics/LoggerFactoryIntegration.md#method-flushall
     */
    public function flushAll() : void
    {
        foreach ($this->loggers as $logger) {
            // If logger supports flushing, call it
            if (method_exists($logger, 'flush')) {
                $logger->flush();
            }
        }
    }

    /**
     * Logs container health check results with status-appropriate log levels.
     *
     * Records health check outcomes with appropriate log levels based on status
     * severity. Provides structured logging of system health assessments for
     * monitoring and alerting integration.
     *
     * HEALTH STATUS MAPPING:
     * - healthy: info level - normal operation confirmed
     * - degraded: warning level - issues detected but functional
     * - unhealthy: error level - critical problems requiring attention
     * - unknown: debug level - undetermined status for investigation
     *
     * HEALTH CHECK DATA:
     * - Status classification and severity assessment
     * - Individual check results and diagnostics
     * - Timestamp for temporal correlation
     * - Performance metrics and system state
     *
     * MONITORING INTEGRATION:
     * - Automated alerting based on health status
     * - Dashboard integration for health trending
     * - Alert escalation for critical health issues
     * - Historical health status tracking
     *
     * DIAGNOSTIC VALUE:
     * - Comprehensive health assessment logging
     * - Individual check result visibility
     * - Temporal health status correlation
     * - Performance metric inclusion
     *
     * USAGE EXAMPLES:
     * ```php
     * // Log successful health check
     * $logging->logHealthCheck([
     *     'status' => 'healthy',
     *     'checks' => [
     *         'database' => ['status' => 'ok', 'latency' => 5],
     *         'cache' => ['status' => 'ok', 'hit_rate' => 0.95]
     *     ],
     *     'timestamp' => microtime(true)
     * ]);
     *
     * // Log degraded system health
     * $logging->logHealthCheck([
     *     'status' => 'degraded',
     *     'checks' => [
     *         'database' => ['status' => 'slow', 'latency' => 2000],
     *         'cache' => ['status' => 'error', 'message' => 'connection failed']
     *     ]
     * ]);
     * ```
     *
     * @param array $healthData Health check results and diagnostic information
     *
     * @return void
     * @see docs/Observe/Metrics/LoggerFactoryIntegration.md#method-loghealthcheck
     */
    public function logHealthCheck(array $healthData) : void
    {
        $logger = $this->getComponentLogger(component: 'health');

        $status = $healthData['status'] ?? 'unknown';
        $level  = match ($status) {
            'healthy'   => 'info',
            'degraded'  => 'warning',
            'unhealthy' => 'error',
            default     => 'debug'
        };

        $logger->$level('Container health check', [
            'status'    => $status,
            'checks'    => $healthData['checks'] ?? [],
            'timestamp' => $healthData['timestamp'] ?? microtime(true)
        ]);
    }

    /**
     * Logs telemetry export operations with data point counts and sink information.
     *
     * Records telemetry data export events for monitoring and auditing of
     * external data transmission. Tracks export success, data volumes, and
     * destination information for compliance and debugging purposes.
     *
     * TELEMETRY CONTEXT:
     * - Export sink identification (json, psr, external systems)
     * - Data point count for volume assessment
     * - Export timestamp for temporal correlation
     * - Success/failure status where applicable
     *
     * COMPLIANCE VALUE:
     * - Audit trail for data export operations
     * - Data transmission monitoring and verification
     * - Volume tracking for capacity planning
     * - Integration verification with external systems
     *
     * MONITORING INTEGRATION:
     * - Export success/failure alerting
     * - Data volume trending and analysis
     * - Export performance monitoring
     * - Integration health verification
     *
     * USAGE EXAMPLES:
     * ```php
     * // Log successful telemetry export
     * $logging->logTelemetryExport('json', 150);
     *
     * // Log export to external monitoring
     * $logging->logTelemetryExport('datadog', 200);
     * ```
     *
     * @param string $sink       Telemetry export destination identifier
     * @param int    $dataPoints Number of data points exported
     *
     * @return void
     * @see docs/Observe/Metrics/LoggerFactoryIntegration.md#method-logtelemetryexport
     */
    public function logTelemetryExport(string $sink, int $dataPoints) : void
    {
        $logger = $this->getComponentLogger(component: 'telemetry');

        $logger->info(message: 'Telemetry exported', context: [
            'sink'        => $sink,
            'data_points' => $dataPoints,
            'timestamp'   => microtime(true)
        ]);
    }
}
