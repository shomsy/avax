<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities;

interface Job
{
    public function getId(): string;

    public function getPayload(): array;

    public function attempts(): int;

    public function release(int $delay = 0): void;

    public function delete(): void;
}
