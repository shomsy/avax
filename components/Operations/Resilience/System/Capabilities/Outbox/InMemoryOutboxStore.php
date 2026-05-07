<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Outbox;

final class InMemoryOutboxStore implements OutboxStore
{
    /**
     * @var list<array{id: string, event: string, payload: array, processed: bool, createdAt: int}>
     */
    private array $entries = [];

    public function enqueue(string $event, array $payload) : void
    {
        $this->entries[] = [
            'id'        => bin2hex(random_bytes(8)),
            'event'     => $event,
            'payload'   => $payload,
            'processed' => false,
            'createdAt' => time(),
        ];
    }

    public function dequeue(int $limit = 10) : array
    {
        $pending = array_filter($this->entries, fn (array $entry) : bool => ! $entry['processed']);

        return array_slice(array_values($pending), 0, $limit);
    }

    public function markProcessed(string $id) : void
    {
        foreach ($this->entries as $i => $entry) {
            if ($entry['id'] === $id) {
                $this->entries[$i]['processed'] = true;
                break;
            }
        }
    }

    public function pendingCount() : int
    {
        return count(array_filter($this->entries, fn (array $entry) : bool => ! $entry['processed']));
    }
}
