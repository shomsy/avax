<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataFoundation\Values;

use Avax\DataFoundation\Values\Identity\Uuid;
use Avax\DataFoundation\Values\Money\Currency;
use Avax\DataFoundation\Values\Money\Money;
use Avax\DataFoundation\Values\Numbers\PositiveInt;
use Avax\DataFoundation\Values\Option\Option;
use Avax\DataFoundation\Values\Result\Result;
use Avax\DataFoundation\Values\Text\NonEmptyString;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class SemanticValuesTest extends TestCase
{
    public function testOptionMapsPresentValue() : void
    {
        $option = Option::some(value: 10)->map(static fn (int $value) : int => $value * 2);

        $this->assertTrue($option->isSome());
        $this->assertSame(20, $option->unwrap());
    }

    public function testResultMapsSuccessfulValue() : void
    {
        $result = Result::ok(value: 5)->map(static fn (int $value) : int => $value + 1);

        $this->assertTrue($result->isOk());
        $this->assertSame(6, $result->unwrap());
    }

    public function testNonEmptyStringPreservesValue() : void
    {
        $value = new NonEmptyString(value: 'Alice');

        $this->assertSame('Alice', $value->value());
        $this->assertSame(5, $value->length());
    }

    public function testPositiveIntRejectsZero() : void
    {
        $this->expectException(RuntimeException::class);
        new PositiveInt(value: 0);
    }

    public function testUuidCanBeGenerated() : void
    {
        $uuid = Uuid::generate();

        $this->assertNotSame('', $uuid->value());
    }

    public function testMoneyAddsOnlySameCurrency() : void
    {
        $money = new Money(amount: 100, currency: new Currency(code: 'EUR'))
            ->add(new Money(amount: 50, currency: new Currency(code: 'EUR')));

        $this->assertSame(150, $money->amount());
        $this->assertSame('EUR', $money->currency()->code());
    }
}
