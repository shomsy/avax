<?php

declare(strict_types=1);

namespace Avax\DataLayer\PropagateDataChanges;

final readonly class PropagateDataChanges
{
    public function __construct(
        private ReadChangeStream       $reader,
        private PublishOutboxMessage   $publisher,
        private RetryChangePublication $retry
    ) {}

    public function describeResponsibility() : string
    {
        return 'propagates data changes to downstream systems using CDC and outbox pattern.';
    }

    public function propagate(int $batchSize = 100) : PropagateDataChangesResult
    {
        return new PropagateDataChangesResult(
            published : 0,
            failed    : 0,
            cursor    : $this->reader->cursor,
            durationMs: 0
        );
    }

    public function toMetadata() : array
    {
        return [
            'reader'    => $this->reader->describeResponsibility(),
            'publisher' => $this->publisher->describeResponsibility(),
            'retry'     => $this->retry->describeResponsibility(),
        ];
    }
}

final readonly class PropagateDataChangesResult
{
    public function __construct(
        public int                $published,
        public int                $failed,
        public ChangeStreamCursor $cursor,
        public int                $durationMs
    ) {}

    public function isSuccess() : bool
    {
        return $this->failed === 0;
    }
}