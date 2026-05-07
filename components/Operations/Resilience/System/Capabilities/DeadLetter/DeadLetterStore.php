<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\DeadLetter;

interface DeadLetterStore
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function store(string $message, array $metadata, string $reason) : void;

    public function count() : int;

    /**
     * @return list<array<string, mixed>>
     */
    public function all() : array;
}
