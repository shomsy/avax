<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Job;

final readonly class JobDefinition
{
    public function __construct(
        public string $handler,
        public array $payload = [],
        public string|null $queue = null,
        public int $maxAttempts = 3,
        public int $timeout = 60,
        public int $retryDelay = 0,
        public string|null $correlationId = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            handler      : $data['handler'],
            payload      : $data['payload'] ?? [],
            queue        : $data['queue'] ?? null,
            maxAttempts  : $data['maxAttempts'] ?? 3,
            timeout      : $data['timeout'] ?? 60,
            retryDelay   : $data['retryDelay'] ?? 0,
            correlationId: $data['correlationId'] ?? null,
        );
    }

    public function withPayload(array $payload): self
    {
        return new self(
            handler      : $this->handler,
            payload      : $payload,
            queue        : $this->queue,
            maxAttempts  : $this->maxAttempts,
            timeout      : $this->timeout,
            retryDelay   : $this->retryDelay,
            correlationId: $this->correlationId,
        );
    }

    public function onQueue(string $queue): self
    {
        return new self(
            handler      : $this->handler,
            payload      : $this->payload,
            queue        : $queue,
            maxAttempts  : $this->maxAttempts,
            timeout      : $this->timeout,
            retryDelay   : $this->retryDelay,
            correlationId: $this->correlationId,
        );
    }

    public function withMaxAttempts(int $maxAttempts): self
    {
        return new self(
            handler      : $this->handler,
            payload      : $this->payload,
            queue        : $this->queue,
            maxAttempts  : $maxAttempts,
            timeout      : $this->timeout,
            retryDelay   : $this->retryDelay,
            correlationId: $this->correlationId,
        );
    }

    public function withTimeout(int $timeout): self
    {
        return new self(
            handler      : $this->handler,
            payload      : $this->payload,
            queue        : $this->queue,
            maxAttempts  : $this->maxAttempts,
            timeout      : $timeout,
            retryDelay   : $this->retryDelay,
            correlationId: $this->correlationId,
        );
    }

    public function withRetryDelay(int $delay): self
    {
        return new self(
            handler      : $this->handler,
            payload      : $this->payload,
            queue        : $this->queue,
            maxAttempts  : $this->maxAttempts,
            timeout      : $this->timeout,
            retryDelay   : $delay,
            correlationId: $this->correlationId,
        );
    }

    public function withCorrelationId(string $id): self
    {
        return new self(
            handler      : $this->handler,
            payload      : $this->payload,
            queue        : $this->queue,
            maxAttempts  : $this->maxAttempts,
            timeout      : $this->timeout,
            retryDelay   : $this->retryDelay,
            correlationId: $id,
        );
    }

    public function toArray(): array
    {
        return [
            'handler' => $this->handler,
            'payload' => $this->payload,
            'queue' => $this->queue,
            'maxAttempts' => $this->maxAttempts,
            'timeout' => $this->timeout,
            'retryDelay' => $this->retryDelay,
            'correlationId' => $this->correlationId ?? uniqid('corr-', true),
            'createdAt' => time(),
        ];
    }
}
