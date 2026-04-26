<?php

declare(strict_types=1);

namespace components\DataLayer\DistributeStoredData;

final readonly class ChooseConsistentHashRing
{
    public function __construct(
        private ConsistentHashRing $ring
    ) {}

    public function describeResponsibility() : string
    {
        return 'chooses a consistent hash ring for key-based routing.';
    }

    public function chooseRing(string $key) : ConsistentHashRing
    {
        return $this->ring;
    }

    public function getNode(string $key) : string
    {
        return $this->ring->getNode(key: $key);
    }

    public function toMetadata() : array
    {
        return $this->ring->toMetadata();
    }
}