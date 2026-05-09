<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Flows\PublishToOutbox;

use Avax\Components\Operations\MessageBus\System\Capabilities\Envelope\MessageEnvelope;
use Avax\Components\Operations\Resilience\System\Capabilities\Outbox\OutboxStore;

/**
 * Publishes a message envelope to the outbox store for eventual delivery.
 *
 * Implements the outbox pattern: write to outbox within the same transaction
 * as the primary operation, then a separate process dispatches the messages.
 */
final readonly class PublishToOutbox
{
    public function __construct(
        private OutboxStore $store,
    ) {}

    /**
     * @param array<string, mixed> $body
     */
    public function execute(string $eventType, array $body, ?string $correlationId = null) : MessageEnvelope
    {
        $envelope = MessageEnvelope::create(
            type: $eventType,
            body: $body,
            correlationId: $correlationId,
        );

        $this->store->enqueue(
            event: $envelope->messageId,
            payload: $envelope->toArray(),
        );

        return $envelope;
    }
}
