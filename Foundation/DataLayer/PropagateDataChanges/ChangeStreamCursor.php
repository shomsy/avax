<?php

declare(strict_types=1);

namespace Avax\DataLayer\PropagateDataChanges;

use InvalidArgumentException;

final readonly class ChangeStreamCursor
{
    public function __construct(
        public string  $streamName,
        public ?string $position,
        public ?string $timestamp,
        public int     $sequence
    ) {}

    public function describeResponsibility() : string
    {
        return 'records where CDC reading should resume.';
    }

    public static function start(string $streamName) : self
    {
        return new self($streamName, null, null, 0);
    }

    public static function fromPosition(string $streamName, string $position) : self
    {
        return new self($streamName, $position, null, 0);
    }

    public function advance(string $position, int $sequence) : self
    {
        return new self(
            $this->streamName,
            $position,
            date('c'),
            $sequence
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