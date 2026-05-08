<?php

declare(strict_types=1);

namespace Avax\Examples\GoldenPathRuntimeApp\Domain\Message;

use Avax\Components\Operations\MessageBus\System\PublicSurface\Command;

final readonly class IngestWebhook implements Command
{
    public function __construct(
        public string $webhookId,
        public string $source,
        public string $payload,
        public string $correlationId,
    ) {}
}
