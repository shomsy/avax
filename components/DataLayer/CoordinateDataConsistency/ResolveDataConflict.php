<?php

declare(strict_types=1);

namespace Avax\DataLayer\CoordinateDataConsistency;

final readonly class ResolveDataConflict
{
    public function __construct(
        private ConflictResolution $resolution
    ) {}

    public function describeResponsibility() : string
    {
        return 'resolves data conflicts using the configured conflict resolution strategy.';
    }

    public function resolve(mixed $localValue, mixed $remoteValue) : ConflictResolutionResult
    {
        try {
            $resolved = $this->resolution->resolve(conflictingValues: [$localValue, $remoteValue]);

            return new ConflictResolutionResult(
                resolved     : true,
                resolvedValue: $resolved,
                strategy     : $this->resolution->strategy,
                timestamp    : microtime(true)
            );
        } catch (ConflictResolutionException $e) {
            return new ConflictResolutionResult(
                resolved     : false,
                resolvedValue: null,
                strategy     : $this->resolution->strategy,
                timestamp    : microtime(true),
                error        : $e->getMessage()
            );
        }
    }

    public function toMetadata() : array
    {
        return $this->resolution->toMetadata();
    }
}

final readonly class ConflictResolutionResult
{
    public function __construct(
        public bool                       $resolved,
        public mixed                      $resolvedValue,
        public ConflictResolutionStrategy $strategy,
        public float                      $timestamp,
        public string|null                $error = null
    ) {}
}