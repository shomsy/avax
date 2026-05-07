<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Outbox;

interface OutboxStore
{
    public function enqueue(string $event, array $payload) : void;

    public function dequeue(int $limit = 10) : array;

    public function markProcessed(string $id) : void;

    public function pendingCount() : int;
}
