<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Flows\DispatchWebhook;

use Avax\Components\API\Surface\System\Capabilities\Webhooks\WebhookDispatcher;

final readonly class DispatchWebhook
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return array<int, array{url: string, delivered: bool, attempts: int}>
     */
    public function dispatch(WebhookDispatcher $dispatcher, string $event, array $payload) : array
    {
        return $dispatcher->dispatch($event, $payload);
    }
}
