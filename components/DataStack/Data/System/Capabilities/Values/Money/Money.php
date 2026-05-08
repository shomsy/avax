<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Values\Money;

use InvalidArgumentException;

/**
 * Money Value Object.
 * Migrated from DataFoundation.
 */
final readonly class Money
{
    public function __construct(
        public int $amount,
        public Currency $currency,
    ) {
    }

    public function add(Money $money): self
    {
        if ($this->currency->code !== $money->currency->code) {
            throw new InvalidArgumentException('Currencies must match.');
        }

        return new self($this->amount + $money->amount, $this->currency);
    }

    public function toFloat(): float
    {
        return $this->amount / 100;
    }
}
