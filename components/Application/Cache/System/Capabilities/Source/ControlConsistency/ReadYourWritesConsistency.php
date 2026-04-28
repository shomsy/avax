<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Source\ControlConsistency;

final class ReadYourWritesConsistency
{
    public function __construct(
        private array $writtenKeys = []
    ) {}

    public function recordWrite(string $key, int $timestamp) : void
    {
        $this->writtenKeys[$key] = $timestamp;
    }

    public function isConsistent(string $key, int $currentTimestamp) : bool
    {
        if (! isset($this->writtenKeys[$key])) {
            return true;
        }

        $writeTimestamp = $this->writtenKeys[$key];

        return $writeTimestamp <= $currentTimestamp;
    }

    public function clear() : void
    {
        $this->writtenKeys = [];
    }
}