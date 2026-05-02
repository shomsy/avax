<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Diagnostics\System\PublicSurface;

use Closure;
use Exception;

final class HealthCheck
{
    /** @var array<string, Closure(): CheckResult> */
    private static array $readinessChecks = [];

    public static function register(string $name, Closure $check) : void
    {
        self::$readinessChecks[$name] = $check;
    }

    public static function reset() : void
    {
        self::$readinessChecks = [];
    }

    public static function liveness(): HealthReport
    {
        return new HealthReport(status: 'up', checks: [
            'php'    => new CheckResult(status: 'up', latencyMs: 0.0),
            'memory' => new CheckResult(status: 'up', latencyMs: 0.0, meta: [
                'usage' => memory_get_usage(real_usage: true),
                'peak'  => memory_get_peak_usage(real_usage: true),
            ]),
        ]);
    }

    public static function readiness(): HealthReport
    {
        $checks  = self::$readinessChecks === []
            ? ['process' => static fn () : CheckResult => new CheckResult(status: 'up')]
            : self::$readinessChecks;
        $results = [];

        foreach ($checks as $name => $check) {
            $results[$name] = self::runCheck(name: $name, check: $check);
        }

        $allUp = array_all(
            array   : $results,
            callback: static fn (CheckResult $checkResult) : bool => $checkResult->status === 'up',
        );

        return new HealthReport(
            status: $allUp ? 'up' : 'degraded',
            checks: $results,
        );
    }

    /**
     * @param Closure(): CheckResult $check
     */
    private static function runCheck(string $name, Closure $check) : CheckResult
    {
        $startedAt = hrtime(as_number: true);

        try {
            $result    = $check();
            $latencyMs = (hrtime(as_number: true) - $startedAt) / 1_000_000;

            return $result->withLatency(latencyMs: $result->latencyMs > 0.0 ? $result->latencyMs : $latencyMs);
        } catch (Exception $exception) {
            return new CheckResult(status: 'down', error: sprintf('%s failed: %s', $name, $exception->getMessage()));
        }
    }
}

final readonly class HealthReport
{
    public function __construct(
        public string $status,
        /** @var array<string, CheckResult> */
        public array $checks = []
    ) {
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'checks' => array_map(
                static fn (CheckResult $checkResult) : array => $checkResult->toArray(),
                $this->checks,
            ),
        ];
    }
}

final readonly class CheckResult
{
    /**
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public string $status,
        public float $latencyMs = 0.0,
        public ?string $error = null,
        public array $meta = [],
    ) {}

    public function withLatency(float $latencyMs) : self
    {
        return new self(
            status   : $this->status,
            latencyMs: $latencyMs,
            error    : $this->error,
            meta     : $this->meta,
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'latency_ms' => $this->latencyMs,
            'error'  => $this->error,
            'meta' => $this->meta,
        ];
    }
}
