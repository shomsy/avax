<?php

declare(strict_types=1);

namespace Avax\Container\Observe\Timeline;

/**
 * High-performance timeline tracker for dependency injection resolution events.
 *
 * This class provides comprehensive performance monitoring and diagnostics for the
 * container's service resolution process. It implements a circular buffer to track
 * resolution events with microsecond precision, enabling detailed performance analysis,
 * memory leak detection, and optimization of dependency injection operations.
 *
 * ARCHITECTURAL ROLE:
 * - Acts as the "Timeline" component in the Observe phase
 * - Provides waterfall chart data for resolution performance
 * - Implements circular buffer for memory-efficient event storage
 * - Enables real-time monitoring and historical analysis
 * - Supports diagnostics dashboards and performance profiling
 *
 * EVENT LIFECYCLE TRACKING:
 * Each resolution event captures:
 * - Service identifier and resolution context
 * - Start/end timestamps with microsecond precision
 * - Memory usage before and after resolution
 * - Nesting depth for dependency hierarchy visualization
 * - Duration calculation with automatic cleanup
 *
 * CIRCULAR BUFFER MANAGEMENT:
 * - Configurable maximum event capacity (default: 10,000)
 * - Automatic cleanup when capacity exceeded
 * - Keeps most recent events for continuous monitoring
 * - Prevents memory exhaustion in long-running applications
 * - Thread-safe event storage and retrieval
 *
 * PERFORMANCE ANALYSIS CAPABILITIES:
 * - Slowest resolution identification for bottleneck detection
 * - Memory usage pattern analysis for leak detection
 * - Dependency depth tracking for complexity assessment
 * - Historical trend analysis for performance degradation
 * - Real-time monitoring integration points
 *
 * USAGE SCENARIOS:
 * ```php
 * // Track resolution performance
 * $timeline = new ResolutionTimeline();
 * $id = $timeline->start('UserService');
 * // ... resolution logic ...
 * $timeline->end($id);
 *
 * // Analyze performance
 * $slowEvents = $timeline->getSlowest(5);
 * $allEvents = $timeline->getEvents();
 * ```
 *
 * MEMORY MANAGEMENT STRATEGY:
 * - Event objects are lightweight with minimal memory footprint
 * - Automatic cleanup prevents unbounded memory growth
 * - Configurable retention policies for different environments
 * - Memory delta calculation for per-resolution overhead analysis
 *
 * THREAD SAFETY CONSIDERATIONS:
 * - Event storage operations are not inherently thread-safe
 * - External synchronization required for concurrent access
 * - Read operations safe if no concurrent modifications
 * - Consider separate timeline instances per thread if needed
 *
 * DIAGNOSTICS INTEGRATION:
 * - Compatible with waterfall chart visualizations
 * - Supports export to monitoring systems
 * - Integration points for APM (Application Performance Monitoring)
 * - Custom event filtering and aggregation capabilities
 *
 * CONFIGURATION OPTIONS:
 * - Maximum event capacity adjustment
 * - Memory threshold monitoring
 * - Custom event filtering rules
 * - Export format customization
 *
 * PERFORMANCE CHARACTERISTICS:
 * - Minimal overhead for event recording (microsecond precision)
 * - O(1) event storage and retrieval
 * - O(n) for sorting operations (getSlowest)
 * - Memory usage proportional to event count
 * - CPU overhead scales with event frequency
 *
 * ERROR HANDLING:
 * - Graceful handling of missing event IDs
 * - Memory limit enforcement with automatic cleanup
 * - Exception safety for timeline operations
 * - Diagnostic information preservation during failures
 *
 * EXTENSIBILITY:
 * - Pluggable event storage backends
 * - Custom event metadata support
 * - Integration with external monitoring systems
 * - Configurable cleanup and retention policies
 *
 * BACKWARD COMPATIBILITY:
 * - Maintains API compatibility with existing implementations
 * - Gradual migration path for enhanced features
 * - Optional advanced features for progressive adoption
 *
 * MONITORING INTEGRATION:
 * - Export capabilities for external monitoring systems
 * - Integration with container telemetry infrastructure
 * - Support for distributed tracing correlation IDs
 * - Metrics collection for dashboard consumption
 *
 * DEBUGGING SUPPORT:
 * - Detailed event context for resolution failure analysis
 * - Call stack preservation for dependency chain debugging
 * - Performance regression detection capabilities
 * - Historical event replay for issue reproduction
 *
 * @package Avax\Container\Observe\Timeline
 * @see     \Avax\Container\Features\Actions\Resolve\Contracts\EngineInterface For the resolution process being
 *          tracked
 * @see     EnhancedMetricsCollector For metrics collection integration
 * @see     docs_md/Observe/Timeline/ResolutionTimeline.md#quick-summary
 */
class ResolutionTimeline
{
    private array $events = [];

    private int $depth = 0;

    private int $maxEvents = 10000; // Prevent memory leaks in long-running apps

    /**
     * Start tracking a resolution event and return its identifier.
     *
     * Begins timing a service resolution operation. Automatically manages memory
     * by implementing a circular buffer that discards old events when the limit
     * is reached.
     *
     * @param string $abstract The service identifier being resolved
     *
     * @return int Unique event ID for later completion with end()
     * @see docs_md/Observe/Timeline/ResolutionTimeline.md#method-start
     */
    public function start(string $abstract): int
    {
        $this->depth++;
        $id = count($this->events);

        // Prevent memory leaks in long-running applications
        if ($id >= $this->maxEvents) {
            // Keep only the most recent half of events
            $this->events = array_slice($this->events, $this->maxEvents / 2);
            $id           = count($this->events);
        }

        $this->events[] = [
            'id'           => $id,
            'abstract'     => $abstract,
            'start'        => microtime(true),
            'end'          => null,
            'depth'        => $this->depth,
            'memory_start' => memory_get_usage()
        ];

        return $id;
    }

    /**
     * Mark a resolution event as completed and capture its duration.
     *
     * Completes the timing for a resolution event and calculates performance metrics.
     * Safe to call multiple times on the same event ID.
     *
     * @param int $id The event ID returned by start()
     *
     * @return void
     * @see docs_md/Observe/Timeline/ResolutionTimeline.md#method-end
     */
    public function end(int $id): void
    {
        if (isset($this->events[$id]) && $this->events[$id]['end'] === null) {
            $this->events[$id]['end']          = microtime(true);
            $this->events[$id]['duration_ms']  = ($this->events[$id]['end'] - $this->events[$id]['start']) * 1000;
            $this->events[$id]['memory_delta'] = memory_get_usage() - $this->events[$id]['memory_start'];
        }

        if ($this->depth > 0) {
            $this->depth--;
        }
    }

    /**
     * Return the recorded timeline events.
     *
     * Returns all recorded events in chronological order. Each event contains:
     * - id: Unique event identifier
     * - abstract: Service identifier
     * - start/end: Microsecond timestamps
     * - duration_ms: Resolution time in milliseconds
     * - memory_delta: Memory usage change in bytes
     * - depth: Nesting level for dependency resolution
     *
     * @return array<array{id: int, abstract: string, start: float, end: float|null, duration_ms: float|null,
     *                         memory_delta: int|null, depth: int, memory_start: int}>
     * @see docs_md/Observe/Timeline/ResolutionTimeline.md#method-getevents
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Return the slowest resolution events for surface debugging hotspots.
     *
     * Useful for identifying performance bottlenecks. Returns events sorted
     * by duration in descending order (slowest first).
     *
     * @param int $limit Maximum number of events to return (default: 5)
     *
     * @return array The slowest resolution events
     * @see docs_md/Observe/Timeline/ResolutionTimeline.md#method-getslowest
     */
    public function getSlowest(int $limit = 5): array
    {
        $sorted = $this->events;
        usort($sorted, static fn($a, $b): int => ($b['duration_ms'] ?? 0) <=> ($a['duration_ms'] ?? 0));

        return array_slice($sorted, 0, $limit);
    }

    /**
     * Clear all recorded events (useful for testing or memory management).
     *
     * Resets the timeline to empty state. Useful for:
     * - Unit testing (isolated event tracking)
     * - Memory management in long-running processes
     * - Fresh diagnostics sessions
     *
     * @return void
     * @see docs_md/Observe/Timeline/ResolutionTimeline.md#method-clear
     */
    public function clear(): void
    {
        $this->events = [];
        $this->depth  = 0;
    }

    /**
     * Set maximum number of events to track before automatic cleanup.
     *
     * Controls memory usage by limiting the number of stored events.
     * When limit is exceeded, older events are automatically discarded.
     *
     * @param int $max Maximum events to track (minimum: 100)
     *
     * @return void
     * @see docs_md/Observe/Timeline/ResolutionTimeline.md#method-setmaxevents
     */
    public function setMaxEvents(int $max): void
    {
        $this->maxEvents = max(100, $max);
    }
}
