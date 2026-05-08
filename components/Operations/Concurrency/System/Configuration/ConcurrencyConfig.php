<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Configuration;

final readonly class ConcurrencyConfig
{
    public function __construct(
        public string $runtime = 'current_process',
        public int    $maxConcurrent = 8,
        public int    $defaultTimeoutMs = 3000,
        public bool   $failFast = false,
    ) {}

    public static function fromArray(array $config) : self
    {
        return new self(
            runtime         : $config['runtime'] ?? 'current_process',
            maxConcurrent   : $config['max_concurrent'] ?? 8,
            defaultTimeoutMs: $config['default_timeout_ms'] ?? 3000,
            failFast        : $config['fail_fast'] ?? false,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'runtime'            => $this->runtime,
            'max_concurrent'     => $this->maxConcurrent,
            'default_timeout_ms' => $this->defaultTimeoutMs,
            'fail_fast'          => $this->failFast,
        ];
    }

    public function isFiberRuntime() : bool
    {
        return $this->runtime === 'fiber';
    }

    public function isCurrentProcessRuntime() : bool
    {
        return $this->runtime === 'current_process';
    }

    public function withRuntime(string $runtime) : self
    {
        return new self(
            runtime         : $runtime,
            maxConcurrent   : $this->maxConcurrent,
            defaultTimeoutMs: $this->defaultTimeoutMs,
            failFast        : $this->failFast,
        );
    }

    public function withMaxConcurrent(int $maxConcurrent) : self
    {
        return new self(
            runtime         : $this->runtime,
            maxConcurrent   : $maxConcurrent,
            defaultTimeoutMs: $this->defaultTimeoutMs,
            failFast        : $this->failFast,
        );
    }
}
