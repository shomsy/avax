<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\HealthCheck\System\PublicSurface;

use Exception;

final readonly class HealthCheck
{
    public static function liveness(): HealthReport
    {
        return new HealthReport(status: 'ok', checks: []);
    }

    public static function readiness(): HealthReport
    {
        $checks = [
            'database' => self::checkDatabase(),
            'cache' => self::checkCache(),
        ];

        $allUp = ! in_array(false, array_column($checks, 'status'));

        return new HealthReport(
            status: $allUp ? 'up' : 'degraded',
            checks: $checks,
        );
    }

    private static function checkDatabase(): CheckResult
    {
        try {
            return new CheckResult(status: 'up', latencyMs: 0.0);
        } catch (Exception $exception) {
            return new CheckResult(status: 'down', error: $exception->getMessage());
        }
    }

    private static function checkCache(): CheckResult
    {
        try {
            return new CheckResult(status: 'up', latencyMs: 0.0);
        } catch (Exception $exception) {
            return new CheckResult(status: 'down', error: $exception->getMessage());
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
    public function __construct(
        public string $status,
        public float $latencyMs = 0.0,
        public ?string $error = null,
    ) {}

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'latency_ms' => $this->latencyMs,
            'error'  => $this->error,
        ];
    }
}
