<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Audit;

class AuditEvent
{
    public function __construct(
        public readonly string  $actor,
        public readonly string  $action,
        public readonly string  $target,
        public readonly array   $metadata = [],
        public readonly ?string $timestamp = null,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'actor' => $this->actor,
            'action' => $this->action,
            'target' => $this->target,
            'metadata' => $this->metadata,
            'timestamp' => $this->timestamp ?? date('c'),
        ];
    }
}