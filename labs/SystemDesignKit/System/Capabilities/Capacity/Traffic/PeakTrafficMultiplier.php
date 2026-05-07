<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Traffic;

/**
 * Peak traffic multiplier model.
 *
 * @experimental V3 labs
 */
final readonly class PeakTrafficMultiplier
{
    public function __construct(
        public int $multiplier,
    ) {}

    public function applyTo(int $baseRps) : int
    {
        return $baseRps * $this->multiplier;
    }

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->multiplier < 1) {
            $errors[] = 'peak_multiplier must be >= 1.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}
