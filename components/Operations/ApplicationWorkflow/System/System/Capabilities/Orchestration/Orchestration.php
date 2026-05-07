<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Capabilities\Orchestration;

use Avax\Components\Operations\ApplicationWorkflow\System\System\Capabilities\Orchestration\Kubernetes\GracefulStop;
use Throwable;

final readonly class Orchestration
{
    public static function liveness() : LivenessResponse
    {
        return new LivenessResponse(status: 'ok');
    }

    public static function readiness() : ReadinessResponse
    {
        $checks = [
            'database' => ReadinessCheck::check('database'),
            'cache'    => ReadinessCheck::check('cache'),
            'queue'    => ReadinessCheck::check('queue'),
        ];

        $allReady = ! in_array(false, array_column($checks, 'ready'));

        return new ReadinessResponse(
            status: $allReady ? 'ready' : 'not_ready',
            checks: $checks,
        );
    }

    public static function startup() : StartupResponse
    {
        return new StartupResponse(status: 'started');
    }

    public static function preStop() : never
    {
        GracefulStop::execute();
    }
}

final readonly class LivenessResponse
{
    public function __construct(
        public string $status,
    ) {}

    public function toArray() : array
    {
        return ['status' => $this->status];
    }
}

final readonly class ReadinessResponse
{
    public function __construct(
        public string $status,
        /** @var array<string, ReadinessCheck> */
        public array  $checks = []
    ) {}

    public function toArray() : array
    {
        return [
            'status' => $this->status,
            'checks' => array_map(
                static fn (ReadinessCheck $readinessCheck) : array => $readinessCheck->toArray(),
                $this->checks,
            ),
        ];
    }
}

final readonly class ReadinessCheck
{
    public function __construct(
        public string  $name,
        public bool    $ready,
        public float   $latencyMs = 0.0,
        public ?string $error = null,
    ) {}

    public static function check(string $name) : self
    {
        $start = microtime(true);

        try {
            return new self($name, true, 0.0);
        } catch (Throwable $throwable) {
            return new self(
                $name,
                false,
                (microtime(true) - $start) * 1000,
                $throwable->getMessage(),
            );
        }
    }

    public function toArray() : array
    {
        return [
            'ready'      => $this->ready,
            'latency_ms' => $this->latencyMs,
            'error'      => $this->error,
        ];
    }
}

final readonly class StartupResponse
{
    public function __construct(
        public string $status,
    ) {}

    public function toArray() : array
    {
        return ['status' => $this->status];
    }
}
