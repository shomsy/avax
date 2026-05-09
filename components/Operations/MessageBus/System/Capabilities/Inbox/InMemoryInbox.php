<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Capabilities\Inbox;

/**
 * In-memory inbox for message deduplication.
 *
 * Tracks processed message IDs to prevent duplicate processing.
 * Used in the inbox pattern for exactly-once message handling.
 */
final class InMemoryInbox
{
    /** @var array<string, int> */
    private array $processedIds = [];

    public function __construct(
        private readonly int $ttlSeconds = 3600,
    ) {}

    /**
     * Check if a message ID has already been processed.
     */
    public function isDuplicate(string $messageId) : bool
    {
        $this->evictExpired();

        return isset($this->processedIds[$messageId]);
    }

    /**
     * Mark a message ID as processed.
     */
    public function markProcessed(string $messageId) : void
    {
        $this->processedIds[$messageId] = time();
    }

    /**
     * Get count of tracked message IDs.
     */
    public function size() : int
    {
        return count($this->processedIds);
    }

    /**
     * Clear all tracked IDs.
     */
    public function clear() : void
    {
        $this->processedIds = [];
    }

    private function evictExpired() : void
    {
        $now = time();

        foreach ($this->processedIds as $id => $timestamp) {
            if ($now - $timestamp > $this->ttlSeconds) {
                unset($this->processedIds[$id]);
            }
        }
    }
}
