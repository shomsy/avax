<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Money;

use InvalidArgumentException;

/**
 * Money Value Object.
 * Migrated from DataFoundation.
 */
final readonly class Money
{
    public function __construct(
        public int      $amount,
        public Currency $currency
    ) {}

    public function add(Money $other) : self
    {
        if ($this->currency->code !== $other->currency->code) {
            throw new InvalidArgumentException('Currencies must match.');
        }

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function toFloat() : float
    {
        return $this->amount / 100;
    }
}
