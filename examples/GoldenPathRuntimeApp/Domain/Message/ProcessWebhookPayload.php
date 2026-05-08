<?php

declare(strict_types=1);

namespace Avax\Examples\GoldenPathRuntimeApp\Domain\Message;

use Avax\Components\Operations\MessageBus\System\PublicSurface\Command;

final readonly class ProcessWebhookPayload implements Command
{
    public function __construct(
        public string $webhookId,
        public string $payload,
    ) {}
}
