<?php

declare(strict_types=1);

namespace Avax\Examples\GoldenPathRuntimeApp\Domain\Message;

use Avax\Components\Operations\MessageBus\System\PublicSurface\DomainEvent;

final readonly class WebhookIngested implements DomainEvent
{
    public function __construct(
        public string $webhookId,
        public string $source,
        public string $timestamp,
    ) {}
}
