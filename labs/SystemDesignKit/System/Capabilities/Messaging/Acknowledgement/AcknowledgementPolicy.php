<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Acknowledgement;

/**
 * Acknowledgement policy model.
 *
 * @experimental V3 labs
 */
enum AcknowledgementPolicy: string
{
    case Auto   = 'auto';
    case Manual = 'manual';
    case Batch  = 'batch';

    /**
     * Whether ack is sent immediately after receive.
     */
    public function isAutoAck() : bool
    {
        return $this === self::Auto;
    }

    /**
     * Risk of message loss (auto = high, manual = low, batch = medium).
     */
    public function lossRisk() : string
    {
        return match ($this) {
            self::Auto   => 'high',
            self::Manual => 'low',
            self::Batch  => 'medium',
        };
    }
}
