<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Configuration;

final readonly class ParallelismConfig
{
    public function __construct(
        public string  $runtime = 'current_process',
        public int     $maxWorkers = 8,
        public int     $defaultTimeoutMs = 3000,
        public bool    $failFast = false,
        public ?string $workerScript = null,
    ) {}

    public static function fromArray(array $config) : self
    {
        return new self(
            runtime         : $config['runtime'] ?? 'current_process',
            maxWorkers      : $config['max_workers'] ?? 8,
            defaultTimeoutMs: $config['default_timeout_ms'] ?? 3000,
            failFast        : $config['fail_fast'] ?? false,
            workerScript    : $config['worker_script'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'runtime'            => $this->runtime,
            'max_workers'        => $this->maxWorkers,
            'default_timeout_ms' => $this->defaultTimeoutMs,
            'fail_fast'          => $this->failFast,
            'worker_script'      => $this->workerScript,
        ];
    }

    public function isProcessRuntime() : bool
    {
        return $this->runtime === 'process' || $this->runtime === 'symfony_process';
    }

    public function isCurrentProcessRuntime() : bool
    {
        return $this->runtime === 'current_process';
    }

    public function withRuntime(string $runtime) : self
    {
        return new self(
            runtime         : $runtime,
            maxWorkers      : $this->maxWorkers,
            defaultTimeoutMs: $this->defaultTimeoutMs,
            failFast        : $this->failFast,
            workerScript    : $this->workerScript,
        );
    }

    public function withMaxWorkers(int $maxWorkers) : self
    {
        return new self(
            runtime         : $this->runtime,
            maxWorkers      : $maxWorkers,
            defaultTimeoutMs: $this->defaultTimeoutMs,
            failFast        : $this->failFast,
            workerScript    : $this->workerScript,
        );
    }
}
