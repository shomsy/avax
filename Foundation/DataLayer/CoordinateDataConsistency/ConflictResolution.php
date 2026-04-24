<?php

declare(strict_types=1);

namespace Avax\DataLayer\CoordinateDataConsistency;

enum ConflictResolutionStrategy: string
{
    case LAST_WRITE_WINS = 'last_write_wins';
    case CLIENT_WINS     = 'client_wins';
    case MERGE           = 'merge';
    case REJECT          = 'reject';
}

final readonly class ConflictResolution
{
    public function __construct(
        public ConflictResolutionStrategy $strategy,
        public array                      $fieldPolicies,
        public bool                       $automatic
    ) {}

    public function describeResponsibility() : string
    {
        return 'resolves conflicts using last-write-wins, client-wins, merge, or rejection.';
    }

    public static function lastWriteWins() : self
    {
        return new self(ConflictResolutionStrategy::LAST_WRITE_WINS, [], true);
    }

    public static function fieldBased(array $fieldPolicies) : self
    {
        return new self(ConflictResolutionStrategy::MERGE, $fieldPolicies, true);
    }

    public function resolve(array $conflictingValues) : mixed
    {
        if ($this->strategy === ConflictResolutionStrategy::LAST_WRITE_WINS) {
            return end($conflictingValues);
        }

        if ($this->strategy === ConflictResolutionStrategy::REJECT) {
            throw new ConflictResolutionException('Conflict detected, manual resolution required.');
        }

        return $conflictingValues[0] ?? null;
    }

    public function toMetadata() : array
    {
        return [
            'strategy'       => $this->strategy->value,
            'field_policies' => $this->fieldPolicies,
            'automatic'      => $this->automatic,
        ];
    }
}

class ConflictResolutionException extends \RuntimeException {}