<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Orchestration;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Orchestration\Kubernetes\GracefulStop;
use Throwable;

final readonly class ReadinessCheck
{
    public function __construct(
        public string  $name,
        public bool    $ready,
        public float   $latencyMs = 0.0,
        public ?string $error = null,
    )
    {
    }

    public static function check(string $name): self
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

    public function toArray(): array
    {
        return [
            'ready' => $this->ready,
            'latency_ms' => $this->latencyMs,
            'error' => $this->error,
        ];
    }
}
