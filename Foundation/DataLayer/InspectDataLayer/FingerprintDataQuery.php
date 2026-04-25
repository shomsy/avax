<?php

declare(strict_types=1);

namespace Avax\DataLayer\InspectDataLayer;

final readonly class FingerprintDataQuery
{
    public function __construct(
        private string $normalizationPattern
    ) {}

    public function describeResponsibility() : string
    {
        return 'fingerprints data query for identification and caching.';
    }

    public function fingerprint(string $query, array $params = []) : string
    {
        $normalized = $this->normalize(query: $query);
        $key        = hash('xxh64', $normalized . serialize($params));

        return $key;
    }

    private function normalize(string $query) : string
    {
        return trim(preg_replace('/\s+/', ' ', $query));
    }

    public function toMetadata() : array
    {
        return ['normalization_pattern' => $this->normalizationPattern];
    }
}