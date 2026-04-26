<?php

declare(strict_types=1);

namespace Avax\DataLayer\PropagateDataChanges;

final readonly class ReadChangeStream
{
    public function __construct(
        private ChangeStreamCursor $cursor
    ) {}

    public function describeResponsibility() : string
    {
        return 'reads change stream from cursor position.';
    }

    public function read(int $batchSize = 100) : ReadChangeStreamResult
    {
        return new ReadChangeStreamResult(
            cursor   : $this->cursor,
            changes  : [],
            hasMore  : false,
            readCount: 0
        );
    }

    public function advance(ChangeStreamCursor $cursor) : ChangeStreamCursor
    {
        return $cursor;
    }

    public function toMetadata() : array
    {
        return $this->cursor->toMetadata();
    }
}

final readonly class ReadChangeStreamResult
{
    public function __construct(
        public ChangeStreamCursor $cursor,
        public array              $changes,
        public bool               $hasMore,
        public int                $readCount
    ) {}

    public function isEmpty() : bool
    {
        return empty($this->changes);
    }
}