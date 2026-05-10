<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Capabilities\Consumer;

use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\EventBus;
use Avax\Components\Operations\MessageBus\System\Capabilities\Envelope\MessageEnvelope;
use Avax\Components\Operations\Resilience\System\Capabilities\Outbox\OutboxStore;
use Closure;

/**
 * Message consumer that processes outbox messages and dispatches them.
 *
 * Reads pending messages from the outbox store, wraps them in envelopes,
 * and dispatches them via the EventBus. Marks successfully dispatched
 * messages as processed.
 */
final readonly class Consumer
{
    public function __construct(
        private OutboxStore $outboxStore,
        private EventBus $eventBus,
        private int $batchSize = 10,
    ) {}

    /**
     * Process a batch of pending outbox messages.
     *
     * @return array{dispatched: int, failed: int, errors: list<array{id: string, error: string}>}
     */
    public function consume() : array
    {
        $messages = $this->outboxStore->dequeue($this->batchSize);

        $dispatched = 0;
        $failed = 0;
        $errors = [];

        foreach ($messages as $message) {
            $id = $message['id'] ?? 'unknown';

            try {
                $envelope = $this->createEnvelope($message['payload'] ?? []);
                $this->eventBus->dispatch($envelope);
                $this->outboxStore->markProcessed($id);
                $dispatched++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = ['id' => $id, 'error' => $e->getMessage()];
            }
        }

        return [
            'dispatched' => $dispatched,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * Consume all pending messages in batches until the queue is empty.
     *
     * @param int<1, 1000> $maxBatches Maximum batches to process (prevents infinite loops)
     * @return array{totalDispatched: int, totalFailed: int, errors: list<array{id: string, error: string}>}
     */
    public function consumeAll(int $maxBatches = 100) : array
    {
        $totalDispatched = 0;
        $totalFailed = 0;
        $allErrors = [];

        for ($i = 0; $i < $maxBatches; $i++) {
            $result = $this->consume();

            if ($result['dispatched'] === 0 && $result['failed'] === 0) {
                break;
            }

            $totalDispatched += $result['dispatched'];
            $totalFailed += $result['failed'];
            $allErrors = [...$allErrors, ...$result['errors']];
        }

        return [
            'totalDispatched' => $totalDispatched,
            'totalFailed' => $totalFailed,
            'errors' => $allErrors,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function createEnvelope(array $payload) : MessageEnvelope
    {
        $type = $payload['type'] ?? $payload['event_type'] ?? 'unknown';
        $body = $payload['body'] ?? $payload;
        $correlationId = $payload['correlation_id'] ?? null;

        return MessageEnvelope::create(
            type: $type,
            body: $body,
            correlationId: $correlationId,
        );
    }

    /**
     * Get count of pending messages.
     */
    public function pendingCount() : int
    {
        return $this->outboxStore->pendingCount();
    }
}
