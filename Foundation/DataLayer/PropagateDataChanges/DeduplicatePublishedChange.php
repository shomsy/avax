<?php

declare(strict_types=1);

namespace Avax\DataLayer\PropagateDataChanges;

final readonly class DeduplicatePublishedChange
{
    public function __construct(
        private int $deduplicationWindowSeconds
    ) {}

    public function describeResponsibility() : string
    {
        return 'deduplicates published changes within a time window.';
    }

    public function shouldDeduplicate(PublishedChange $change, array $seenIds) : bool
    {
        return in_array($change->id, $seenIds, true);
    }

    public function generateKey(PublishedChange $change) : string
    {
        return sprintf(
            '%s:%s:%s:%s',
            $change->streamName,
            $change->table,
            $change->type->value,
            $change->after['id'] ?? $change->before['id'] ?? ''
        );
    }

    public function toMetadata() : array
    {
        return ['deduplication_window_seconds' => $this->deduplicationWindowSeconds];
    }
}