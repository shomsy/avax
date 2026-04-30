<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities;

use DateTimeInterface;

interface QueueDriverInterface
{
    public function push(string $job, array $data = []): string;

    public function later(DateTimeInterface $delay, string $job, array $data = []) : string;
    public function pop(): ?Job;
    public function size(string $queue = 'default'): int;
    public function bulk(array $jobs, string $queue = 'default'): void;
}

interface Job
{
    public function getId(): string;
    public function getPayload(): array;
    public function attempts(): int;
    public function release(int $delay = 0): void;
    public function delete(): void;
}
