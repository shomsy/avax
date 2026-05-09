<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\Consistency\Delivery;

/**
 * Message delivery semantics taxonomy.
 *
 * @experimental V3 labs
 *
 * Describes the consistency guarantee provided by a delivery mechanism.
 */
enum DeliverySemantics: string
{
    case AtMostOnce          = 'at_most_once';
    case AtLeastOnce         = 'at_least_once';
    case ExactlyOnceIllusion = 'exactly_once_illusion';

    /**
     * Whether duplicate messages are possible.
     */
    public function allowsDuplicates() : bool
    {
        return $this === self::AtLeastOnce;
    }

    /**
     * Whether message loss is possible.
     */
    public function allowsLoss() : bool
    {
        return $this === self::AtMostOnce;
    }

    /**
     * Human-readable description of the guarantee.
     */
    public function description() : string
    {
        return match ($this) {
            self::AtMostOnce          => 'Message delivered at most once; may be lost but never duplicated.',
            self::AtLeastOnce         => 'Message delivered at least once; may be duplicated but never lost.',
            self::ExactlyOnceIllusion => 'Effectively-once via idempotency and deduplication; not true exactly-once.',
        };
    }

    /**
     * Estimated overhead cost (low, medium, high).
     *
     * At-most-once has lowest overhead.
     * Exactly-once illusion has highest overhead (idempotency keys, dedup).
     */
    public function overheadCost() : string
    {
        return match ($this) {
            self::AtMostOnce          => 'low',
            self::AtLeastOnce         => 'medium',
            self::ExactlyOnceIllusion => 'high',
        };
    }
}
