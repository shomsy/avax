<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware\System\Configuration;

/**
 * Immutable configuration object for the Middleware component.
 *
 * Controls middleware pipeline behavior such as timeout,
 * error handling, and priority ordering.
 */
final readonly class MiddlewareConfiguration
{
    /**
     * @param  int  $timeoutMs  Maximum execution time for the entire pipeline in milliseconds
     * @param  bool  $stopOnException  Whether to halt the pipeline on middleware exception
     * @param  list<string>  $priorityOrder  Ordered list of middleware identifiers (first = highest priority)
     * @param  bool  $enableMetrics  Whether to collect per-middleware execution metrics
     * @param  list<string>  $skipPaths  URL paths that bypass all middleware
     */
    public function __construct(
        private int $timeoutMs = 30000,
        private bool $stopOnException = true,
        private array $priorityOrder = [],
        private bool $enableMetrics = false,
        private array $skipPaths = [],
    ) {}

    public function timeoutMs(): int
    {
        return $this->timeoutMs;
    }

    public function shouldStopOnException(): bool
    {
        return $this->stopOnException;
    }

    /**
     * @return list<string>
     */
    public function priorityOrder(): array
    {
        return $this->priorityOrder;
    }

    public function metricsEnabled(): bool
    {
        return $this->enableMetrics;
    }

    /**
     * @return list<string>
     */
    public function skipPaths(): array
    {
        return $this->skipPaths;
    }

    /**
     * Check if a path should skip middleware.
     */
    public function shouldSkipPath(string $path): bool
    {
        foreach ($this->skipPaths as $skipPattern) {
            if ($this->matchesPattern($skipPattern, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Simple glob-like pattern matching.
     */
    private function matchesPattern(string $pattern, string $path): bool
    {
        if ($pattern === $path) {
            return true;
        }

        // Convert glob pattern to regex
        $regex = '#^'.str_replace('\*', '.*', preg_quote($pattern, '#')).'$#';

        return preg_match($regex, $path) === 1;
    }

    /**
     * Create a new configuration with merged overrides.
     *
     * @param  array<string, mixed>  $overrides
     */
    public function with(array $overrides): self
    {
        return new self(
            timeoutMs      : $overrides['timeout_ms'] ?? $this->timeoutMs,
            stopOnException: $overrides['stop_on_exception'] ?? $this->stopOnException,
            priorityOrder  : $overrides['priority_order'] ?? $this->priorityOrder,
            enableMetrics  : $overrides['enable_metrics'] ?? $this->enableMetrics,
            skipPaths      : $overrides['skip_paths'] ?? $this->skipPaths,
        );
    }

    /**
     * Create configuration from an array.
     *
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            timeoutMs      : $config['timeout_ms'] ?? 30000,
            stopOnException: $config['stop_on_exception'] ?? true,
            priorityOrder  : $config['priority_order'] ?? [],
            enableMetrics  : $config['enable_metrics'] ?? false,
            skipPaths      : $config['skip_paths'] ?? [],
        );
    }
}
