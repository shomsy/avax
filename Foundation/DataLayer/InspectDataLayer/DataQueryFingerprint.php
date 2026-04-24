<?php

declare(strict_types=1);

namespace Avax\DataLayer\InspectDataLayer;

final readonly class DataQueryFingerprint
{
    public function __construct(
        public string $fingerprint,
        public string $queryType,
        public array  $tables,
        public array  $conditions
    ) {}

    public function describeResponsibility() : string
    {
        return 'generates data query fingerprint for identification and caching.';
    }

    public static function create(string $query, array $bindings = []) : self
    {
        $queryType = strtoupper(strtok(trim($query), ' '));

        preg_match_all('/FROM\s+(\w+)/i', $query, $matches);
        $tables = $matches[1] ?? [];

        return new self(
            fingerprint: hash('xxh64', $query . serialize($bindings)),
            queryType  : $queryType,
            tables     : $tables,
            conditions : $bindings
        );
    }

    public function toMetadata() : array
    {
        return [
            'fingerprint' => $this->fingerprint,
            'query_type'  => $this->queryType,
            'tables'      => $this->tables,
        ];
    }
}