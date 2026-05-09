<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\Consistency\Delivery;

/**
 * Delivery guarantee with associated risk analysis.
 *
 * @experimental V3 labs
 *
 * Models a delivery path's consistency guarantee,
 * including duplicate risk, loss risk, and dedup requirements.
 */
final readonly class DeliveryGuarantee
{
    public function __construct(
        public string            $path,
        public DeliverySemantics $semantics,
        public bool              $idempotencyRequired,
        public bool              $dedupRequired,
        public int               $dedupWindowSeconds,
    ) {}

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->path === '') {
            $errors[] = 'path must not be empty.';
        }

        if ($this->semantics === DeliverySemantics::ExactlyOnceIllusion && ! $this->idempotencyRequired) {
            $errors[] = 'exactly-once illusion requires idempotency.';
        }

        if ($this->semantics === DeliverySemantics::AtLeastOnce && ! $this->dedupRequired) {
            $errors[] = 'at-least-once delivery requires deduplication to handle duplicates.';
        }

        if ($this->dedupWindowSeconds < 0) {
            $errors[] = 'dedup_window_seconds must be non-negative.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /**
     * Risk of duplicate messages reaching the consumer.
     */
    public function duplicateRisk() : string
    {
        return match (true) {
            $this->semantics->allowsDuplicates() && ! $this->dedupRequired => 'high',
            $this->semantics->allowsDuplicates() && $this->dedupRequired   => 'low',
            default                                                        => 'none',
        };
    }

    /**
     * Risk of message loss.
     */
    public function lossRisk() : string
    {
        return $this->semantics->allowsLoss() ? 'medium' : 'low';
    }
}
