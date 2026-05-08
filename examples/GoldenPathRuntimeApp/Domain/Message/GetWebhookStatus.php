<?php

declare(strict_types=1);

namespace Avax\Examples\GoldenPathRuntimeApp\Domain\Message;

use Avax\Components\Operations\MessageBus\System\PublicSurface\Query;

final readonly class GetWebhookStatus implements Query
{
    public function __construct(
        public string $webhookId,
    ) {}
}
