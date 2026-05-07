<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\DeadLetter;

final class InMemoryDeadLetterStore implements DeadLetterStore
{
    /**
     * @var list<array<string, mixed>>
     */
    private array $letters = [];

    /**
     * @param array<string, mixed> $metadata
     */
    public function store(string $message, array $metadata, string $reason) : void
    {
        $this->letters[] = [
            'message'  => $message,
            'metadata' => $metadata,
            'reason'   => $reason,
            'storedAt' => time(),
        ];
    }

    public function count() : int
    {
        return count($this->letters);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function all() : array
    {
        return $this->letters;
    }

    public function clear() : void
    {
        $this->letters = [];
    }
}
