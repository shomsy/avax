<?php

declare(strict_types=1);

namespace Avax\DataLayer\PropagateDataChanges;

final readonly class ChangeStreamCursor
{
    public function __construct(
        public string      $streamName,
        public string|null $position,
        public string|null $timestamp,
        public int         $sequence
    ) {}

    public static function start(string $streamName) : self
    {
        return new self(streamName: $streamName, position: null, timestamp: null, sequence: 0);
    }

    public static function fromPosition(string $streamName, string $position) : self
    {
        return new self(streamName: $streamName, position: $position, timestamp: null, sequence: 0);
    }

    public function describeResponsibility() : string
    {
        return 'records where CDC reading should resume.';
    }

    public function advance(string $position, int $sequence) : self
    {
        return new self(
            streamName: $this->streamName,
            position  : $position,
            timestamp : date('c'),
            sequence  : $sequence
        );
    }

    public function isStart() : bool
    {
        return $this->position === null;
    }

    public function toMetadata() : array
    {
        return [
            'stream_name' => $this->streamName,
            'position'    => $this->position,
            'timestamp'   => $this->timestamp,
            'sequence'    => $this->sequence,
        ];
    }
}