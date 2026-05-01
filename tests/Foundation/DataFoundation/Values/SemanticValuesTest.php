<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataFoundation\Values;

use Avax\Components\DataStack\Database\Values\Identity\Uuid;
use Avax\Components\DataStack\Database\Values\Money\Currency;
use Avax\Components\DataStack\Database\Values\Money\Money;
use Avax\Components\DataStack\Database\Values\Numbers\PositiveInt;
use Avax\Components\DataStack\Database\Values\Option\Option;
use Avax\Components\DataStack\Database\Values\Result\Result;
use Avax\Components\DataStack\Database\Values\Text\NonEmptyString;
use Avax\Tests\TestCase;
use RuntimeException;

final class SemanticValuesTest extends TestCase
{
    public function test_option_maps_present_value() : void
    {
        $option = Option::some(value: 10)->map(callback: static fn (int $value) : int => $value * 2);

        $this->assertTrue($option->isSome());
        $this->assertSame(20, $option->unwrap());
    }

    public function test_result_maps_successful_value() : void
    {
        $result = Result::ok(value: 5)->map(callback: static fn (int $value) : int => $value + 1);

        $this->assertTrue($result->isOk());
        $this->assertSame(6, $result->unwrap());
    }

    public function test_non_empty_string_preserves_value() : void
    {
        $value = new NonEmptyString(value: 'Alice');

        $this->assertSame('Alice', $value->value());
        $this->assertSame(5, $value->length());
    }

    public function test_positive_int_rejects_zero() : void
    {
        $this->expectException(RuntimeException::class);
        new PositiveInt(value: 0);
    }

    public function test_uuid_can_be_generated() : void
    {
        $uuid = Uuid::generate();

        $this->assertNotSame('', $uuid->value());
    }

    public function test_money_adds_only_same_currency() : void
    {
        $money = new Money(amount: 100, currency: new Currency(code: 'EUR'))
            ->add(other: new Money(amount: 50, currency: new Currency(code: 'EUR')));

        $this->assertSame(150, $money->amount());
        $this->assertSame('EUR', $money->currency()->code());
    }
}
