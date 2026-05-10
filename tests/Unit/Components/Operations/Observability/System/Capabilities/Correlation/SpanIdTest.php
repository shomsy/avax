<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Observability\System\Capabilities\Correlation;

use Avax\Components\Operations\Observability\System\Capabilities\Correlation\SpanId;
use Avax\Components\Operations\Observability\System\Capabilities\Correlation\TraceId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Stringable;

final class SpanIdTest extends TestCase
{
    #[Test]
    public function it_generates_unique_ids() : void
    {
        $id1 = SpanId::generate();
        $id2 = SpanId::generate();

        self::assertNotSame($id1->value, $id2->value);
    }

    #[Test]
    public function it_generates_hexadecimal_value() : void
    {
        $id = SpanId::generate();

        self::assertMatchesRegularExpression('/^[0-9a-f]+$/', $id->value);
    }

    #[Test]
    public function it_generates_16_character_hex_string() : void
    {
        $id = SpanId::generate();

        self::assertSame(16, strlen($id->value));
    }

    #[Test]
    public function it_creates_from_string() : void
    {
        $id = SpanId::fromString('my-span-id');

        self::assertSame('my-span-id', $id->value);
    }

    #[Test]
    public function it_creates_from_empty_string() : void
    {
        $id = SpanId::fromString('');

        self::assertSame('', $id->value);
    }

    #[Test]
    public function it_converts_to_string() : void
    {
        $id = SpanId::fromString('test-span');

        self::assertSame('test-span', (string) $id);
    }

    #[Test]
    public function it_implements_stringable() : void
    {
        $id = SpanId::generate();

        self::assertInstanceOf(Stringable::class, $id);
    }

    #[Test]
    public function it_is_readonly() : void
    {
        $reflection = new ReflectionClass(SpanId::class);

        self::assertTrue($reflection->isReadonly());
    }

    #[Test]
    public function it_is_final() : void
    {
        $reflection = new ReflectionClass(SpanId::class);

        self::assertTrue($reflection->isFinal());
    }

    #[Test]
    public function it_has_private_constructor() : void
    {
        $reflection  = new ReflectionClass(SpanId::class);
        $constructor = $reflection->getConstructor();

        self::assertNotNull($constructor);
        self::assertTrue($constructor->isPrivate());
    }

    #[Test]
    public function it_generates_cryptographically_secure_bytes() : void
    {
        $values = [];
        for ($i = 0; $i < 100; $i++) {
            $values[] = SpanId::generate()->value;
        }

        $uniqueValues = array_unique($values);

        self::assertCount(100, $uniqueValues);
    }

    #[Test]
    public function it_is_shorter_than_trace_id() : void
    {
        $spanIdLength  = strlen(SpanId::generate()->value);
        $traceIdLength = strlen(TraceId::generate()->value);

        self::assertLessThan($traceIdLength, $spanIdLength);
    }
}
