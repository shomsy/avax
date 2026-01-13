<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Tracing;

/**
 * Performance traceability layer for HTTP router operations.
 *
 * Provides fine-grained introspection and observability for:
 * - Route resolution timing and flow
 * - Matcher branch analysis
 * - Cache operation tracking
 * - Bootstrap performance metrics
 *
 * Enables deterministic debugging and profiling capabilities
 * comparable to Symfony Profiler and Laravel Telescope.
 */
final class RouterTrace
{
    /** @var array<array{time: float, event: string, context: array}> */
    private array $events;

    private float $startTime;

    private int $eventCount;

    public function __construct()
    {
        $this->events = [];
        $this->startTime = microtime(true);
        $this->eventCount = 0;
    }

    /**
     * Log a trace event with timing and context.
     *
     * @param string $event   Event identifier (e.g., 'resolve.start', 'matcher.domain_match')
     * @param array  $context Additional context data for debugging
     */
    public function log(string $event, array $context = []) : void
    {
        $this->events[] = [
            'time'    => round((microtime(true) - $this->startTime) * 1000, 3), // milliseconds
            'event'   => $event,
            'context' => $context,
        ];

        $this->eventCount++;
    }

    /**
     * Enhanced record method with automatic context enrichment.
     *
     * Automatically adds request_id, route metadata, and system context
     * to provide richer debugging information for enterprise observability.
     *
     * @param string $event   Event identifier
     * @param array  $context Base context data
     * @param array  $autoEnrich Automatic enrichment flags
     */
    public function record(
        string $event,
        array $context = [],
        array $autoEnrich = ['request_id', 'memory', 'route']
    ): void {
        $enrichedContext = $context;

        // Auto-enrich with request ID if available
        if (in_array('request_id', $autoEnrich, true)) {
            $enrichedContext['request_id'] = $this->getCurrentRequestId();
        }

        // Auto-enrich with memory usage
        if (in_array('memory', $autoEnrich, true)) {
            $enrichedContext['memory_usage'] = memory_get_usage(true);
            $enrichedContext['memory_peak'] = memory_get_peak_usage(true);
        }

        // Auto-enrich with route information if available in context
        if (in_array('route', $autoEnrich, true)) {
            $enrichedContext = $this->enrichWithRouteContext($event, $enrichedContext);
        }

        // Auto-enrich with system context
        $enrichedContext['php_version'] = PHP_VERSION;
        $enrichedContext['timestamp'] = microtime(true);
        $enrichedContext['process_id'] = getmypid();

        $this->log($event, $enrichedContext);
    }

    /**
     * Get current request ID from various sources.
     */
    private function getCurrentRequestId(): string {
        // Try common request ID headers/sources
        $sources = [
            $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
            $_SERVER['HTTP_X_CORRELATION_ID'] ?? null,
            $_SERVER['REQUEST_ID'] ?? null,
            $_ENV['REQUEST_ID'] ?? null,
        ];

        foreach ($sources as $source) {
            if ($source !== null) {
                return $source;
            }
        }

        // Generate a unique request ID if none found
        return uniqid('req_', true);
    }

    /**
     * Enrich context with route-specific information.
     */
    private function enrichWithRouteContext(string $event, array $context): array {
        // Extract route information from recent events
        $routeInfo = $this->findRecentRouteInfo();

        if ($routeInfo) {
            $context['route_name'] = $routeInfo['name'] ?? null;
            $context['route_method'] = $routeInfo['method'] ?? null;
            $context['route_path'] = $routeInfo['path'] ?? null;
            $context['middleware_count'] = $routeInfo['middleware_count'] ?? 0;
            $context['domain'] = $routeInfo['domain'] ?? null;
        }

        // Add timing information for route resolution events
        if (str_starts_with($event, 'resolve.')) {
            $context['resolution_time_ms'] = $this->calculateResolutionTime();
        }

        return $context;
    }

    /**
     * Find recent route information from trace events.
     */
    private function findRecentRouteInfo(): array|null {
        // Look for the most recent route matching event
        for ($i = count($this->events) - 1; $i >= 0; $i--) {
            $event = $this->events[$i];

            if (isset($event['context']['route'])) {
                $route = $event['context']['route'];
                return [
                    'name' => $route->name ?? null,
                    'method' => $route->method ?? null,
                    'path' => $route->path ?? null,
                    'middleware_count' => count($route->middleware ?? []),
                    'domain' => $route->domain ?? null,
                ];
            }
        }

        return null;
    }

    /**
     * Calculate route resolution time from trace events.
     */
    private function calculateResolutionTime(): float {
        $startTime = null;
        $endTime = null;

        foreach ($this->events as $event) {
            if ($event['event'] === 'resolve.start') {
                $startTime = $event['time'];
            }
            if ($event['event'] === 'resolve.complete' || $event['event'] === 'resolve.failed') {
                $endTime = $event['time'];
                break;
            }
        }

        if ($startTime !== null && $endTime !== null) {
            return round($endTime - $startTime, 3);
        }

        return 0.0;
    }

    /**
     * Export enriched trace data in JSON format for ELK stack.
     */
    public function exportJson(): string {
        $traceData = [
            'trace_id' => $this->getCurrentRequestId(),
            'start_time' => $this->startTime,
            'total_time_ms' => $this->getTotalTime(),
            'event_count' => $this->eventCount,
            'events' => $this->events,
            'system_info' => [
                'php_version' => PHP_VERSION,
                'memory_peak' => memory_get_peak_usage(true),
                'process_id' => getmypid(),
            ],
        ];

        try {
            return json_encode($traceData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        } catch (\JsonException $exception) {
            return json_encode([
                'error' => 'Failed to export trace data',
                'message' => $exception->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }
    }

    /**
     * Get time elapsed between two events.
     *
     * @param string $fromEvent Starting event identifier
     * @param string $toEvent   Ending event identifier
     */
    public function getTimeBetween(string $fromEvent, string $toEvent) : float|null
    {
        $fromTime = null;
        $toTime   = null;

        foreach ($this->events as $event) {
            if ($event['event'] === $fromEvent) {
                $fromTime = $event['time'];
            }
            if ($event['event'] === $toEvent) {
                $toTime = $event['time'];
                break;
            }
        }

        if ($fromTime !== null && $toTime !== null) {
            return round($toTime - $fromTime, 3);
        }

        return null;
    }

    /**
     * Export all trace events for analysis.
     *
     * @return array<array{time: float, event: string, context: array}>
     */
    public function export() : array
    {
        return $this->events;
    }

    /**
     * Get events filtered by event type.
     *
     * @param string $eventType Event identifier to filter by
     *
     * @return array<array{time: float, event: string, context: array}>
     */
    public function getEventsByType(string $eventType) : array
    {
        return array_filter(
            $this->events,
            static fn(array $event) => $event['event'] === $eventType
        );
    }

    /**
     * Get the total number of logged events.
     */
    public function getEventCount() : int
    {
        return $this->eventCount;
    }

    /**
     * Clear all trace events (for testing/reuse).
     */
    public function clear() : void
    {
        $this->events     = [];
        $this->eventCount = 0;
        $this->startTime  = microtime(true);
    }

    /**
     * Export trace data in a human-readable format.
     */
    public function toString() : string
    {
        $output = sprintf("Router Trace (Total: %.3fms, Events: %d)\n", $this->getTotalTime(), $this->eventCount);
        $output .= str_repeat('=', 50) . "\n";

        foreach ($this->events as $event) {
            $output .= sprintf(
                "[%7.3fms] %-25s %s\n",
                $event['time'],
                $event['event'],
                ! empty($event['context']) ? json_encode($event['context'], JSON_UNESCAPED_SLASHES) : ''
            );
        }

        return $output;
    }

    /**
     * Get the total execution time from trace start.
     */
    public function getTotalTime() : float
    {
        return round((microtime(true) - $this->startTime) * 1000, 3);
    }

    /**
     * Check if a specific event was logged.
     */
    public function hasEvent(string $event) : bool
    {
        foreach ($this->events as $loggedEvent) {
            if ($loggedEvent['event'] === $event) {
                return true;
            }
        }

        return false;
    }
}