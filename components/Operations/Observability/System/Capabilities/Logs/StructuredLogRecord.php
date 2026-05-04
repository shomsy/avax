<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Logs;

class StructuredLogRecord
{
    public function __construct(
        public readonly string  $level,
        public readonly string  $message,
        public readonly array   $context = [],
        public readonly ?string $timestamp = null,
        public readonly ?string $requestId = null,
    )
    {
    }

    public function withContext(array $context): self
    {
        return new self(
            $this->level,
            $this->message,
            array_merge($this->context, $context),
            $this->timestamp,
            $this->requestId,
        );
    }

    public function withRequestId(string $requestId): self
    {
        return new self(
            $this->level,
            $this->message,
            $this->context,
            $this->timestamp,
            $requestId,
        );
    }

    public function toArray(): array
    {
        return [
            'level' => $this->level,
            'message' => $this->message,
            'context' => $this->context,
            'timestamp' => $this->timestamp ?? date('c'),
            'request_id' => $this->requestId,
        ];
    }
}