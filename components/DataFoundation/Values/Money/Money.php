<?php

declare(strict_types=1);

namespace components\DataFoundation\Values\Money;

use components\DataFoundation\Exceptions\InvalidValueException;

/**
 * Monetary amount expressed in minor units.
 */
final readonly class Money
{
    public function __construct(
        private int      $amount,
        private Currency $currency,
    ) {}

    public function amount() : int
    {
        return $this->amount;
    }

    public function currency() : Currency
    {
        return $this->currency;
    }

    public function add(self $other) : self
    {
        $this->assertSameCurrency(other: $other);

        return new self(amount: $this->amount + $other->amount, currency: $this->currency);
    }

    private function assertSameCurrency(self $other) : void
    {
        if ((string) $this->currency !== (string) $other->currency) {
            throw InvalidValueException::because(message: 'Money values must use the same currency.');
        }
    }

    public function subtract(self $other) : self
    {
        $this->assertSameCurrency(other: $other);

        return new self(amount: $this->amount - $other->amount, currency: $this->currency);
    }
}
