<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Traffic;

/**
 * Fanout size model — number of downstream operations per request.
 *
 * @experimental V3 labs
 */
final readonly class FanoutSize
{
    public function __construct(
        public int $average,
        public int $max,
    ) {}

    /**
     * Calculate total downstream operations per second.
     */
    public function downstreamOpsPerSecond(int $rps) : int
    {
        return $rps * $this->average;
    }

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->average < 0) {
            $errors[] = 'average fanout must be non-negative.';
        }

        if ($this->max < $this->average) {
            $errors[] = "max fanout ({$this->max}) must be >= average ({$this->average}).";
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}
