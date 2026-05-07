<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\DeadLetter;

interface DeadLetterStore
{
    public function store(string $message, array $metadata, string $reason) : void;

    public function count() : int;

    /**
     * @return list<array{message: string, metadata: array, reason: string, storedAt: int}>
     */
    public function all() : array;
}
