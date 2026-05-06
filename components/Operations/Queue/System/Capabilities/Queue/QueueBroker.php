<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Queue;

interface QueueBroker
{
    public function push(string $queue, array $job): void;

    public function pop(string $queue): ?array;

    public function size(string $queue): int;

    public function remove(string $queue, string $jobId): void;

    public function clear(string $queue): void;
}
