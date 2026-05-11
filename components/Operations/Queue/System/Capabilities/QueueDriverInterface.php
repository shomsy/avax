<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities;

use DateTimeInterface;

interface QueueDriverInterface
{
    public function push(string $job, array $data = []): string;

    public function later(DateTimeInterface $delay, string $job, array $data = []): string;

    public function pop() : Job|null;

    public function size(string $queue = 'default'): int;

    public function bulk(array $jobs, string $queue = 'default'): void;
}
