<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\Consistency\Conflicts;

/**
 * Conflict resolution strategy taxonomy.
 *
 * @experimental V3 labs
 *
 * Defines how write conflicts are resolved when
 * concurrent writes target the same data.
 */
enum ConflictStrategy: string
{
    case LastWriteWins  = 'last_write_wins';
    case FirstWriteWins = 'first_write_wins';
    case VersionVector  = 'version_vector';
    case CRDT           = 'crdt';
    case Manual         = 'manual';
    case Custom         = 'custom';

    /**
     * Whether this strategy requires application-level logic.
     */
    public function requiresApplicationLogic() : bool
    {
        return in_array($this, [
            self::Manual,
            self::Custom,
            self::VersionVector,
            self::CRDT,
        ],              true);
    }

    /**
     * Whether this strategy can cause data loss.
     */
    public function canLoseData() : bool
    {
        return in_array($this, [
            self::LastWriteWins,
            self::FirstWriteWins,
        ],              true);
    }

    /**
     * Estimated complexity cost (low, medium, high).
     */
    public function complexityCost() : string
    {
        return match ($this) {
            self::LastWriteWins, self::FirstWriteWins => 'low',
            self::Manual, self::Custom                => 'medium',
            self::VersionVector                       => 'high',
            self::CRDT                                => 'high',
        };
    }

    /**
     * Human-readable description.
     */
    public function description() : string
    {
        return match ($this) {
            self::LastWriteWins  => 'Latest timestamp wins; earlier writes are lost.',
            self::FirstWriteWins => 'First write wins; concurrent writes are rejected.',
            self::VersionVector  => 'Uses version vectors to detect and merge concurrent writes.',
            self::CRDT           => 'Uses conflict-free replicated data types for automatic merge.',
            self::Manual         => 'Conflicts are surfaced for manual resolution.',
            self::Custom         => 'Custom application-defined conflict resolution.',
        };
    }
}
