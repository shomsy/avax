<?php

declare(strict_types=1);

namespace Avax\DataLayer\DistributeStoredData;

final readonly class RouteByConsistentHash
{
    public function __construct(
        private ConsistentHashRing $ring
    ) {}

    public function describeResponsibility() : string
    {
        return 'routes data by consistent hash ring to determine target node.';
    }

    public function route(mixed $key) : HashRouteResult
    {
        $keyString = $this->serializeKey($key);
        $node      = $this->ring->getNode($keyString);

        return new HashRouteResult(
            key : $keyString,
            node: $node,
            hash: crc32($keyString)
        );
    }

    private function serializeKey(mixed $key) : string
    {
        if (is_array($key)) {
            return implode(':', $key);
        }

        return (string) $key;
    }

    public function toMetadata() : array
    {
        return $this->ring->toMetadata();
    }
}

final readonly class HashRouteResult
{
    public function __construct(
        public string $key,
        public string $node,
        public int    $hash
    ) {}
}