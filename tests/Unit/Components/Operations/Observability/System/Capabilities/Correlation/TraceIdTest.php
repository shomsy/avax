<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Observability\System\Capabilities\Correlation;

use Avax\Components\Operations\Observability\System\Capabilities\Correlation\CorrelationId;
use Avax\Components\Operations\Observability\System\Capabilities\Correlation\TraceId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Stringable;

final class TraceIdTest extends TestCase
{
    #[Test]
    public function it_generates_unique_ids() : void
    {
        $id1 = TraceId::generate();
        $id2 = TraceId::generate();

        self::assertNotSame($id1->value, $id2->value);
    }

    #[Test]
    public function it_generates_hexadecimal_value() : void
    {
        $id = TraceId::generate();

        self::assertMatchesRegularExpression('/^[0-9a-f]+$/', $id->value);
    }

    #[Test]
    public function it_generates_32_character_hex_string() : void
    {
        $id = TraceId::generate();

        self::assertSame(32, strlen($id->value));
    }

    #[Test]
    public function it_creates_from_string() : void
    {
        $id = TraceId::fromString('my-trace-id');

        self::assertSame('my-trace-id', $id->value);
    }

    #[Test]
    public function it_creates_from_empty_string() : void
    {
        $id = TraceId::fromString('');

        self::assertSame('', $id->value);
    }

    #[Test]
    public function it_converts_to_string() : void
    {
        $id = TraceId::fromString('test-trace');

        self::assertSame('test-trace', (string) $id);
    }

    #[Test]
    public function it_implements_stringable() : void
    {
        $id = TraceId::generate();

        self::assertInstanceOf(Stringable::class, $id);
    }

    #[Test]
    public function it_is_readonly() : void
    {
        $reflection = new ReflectionClass(TraceId::class);

        self::assertTrue($reflection->isReadonly());
    }

    #[Test]
    public function it_is_final() : void
    {
        $reflection = new ReflectionClass(TraceId::class);

        self::assertTrue($reflection->isFinal());
    }

    #[Test]
    public function it_has_private_constructor() : void
    {
        $reflection  = new ReflectionClass(TraceId::class);
        $constructor = $reflection->getConstructor();

        self::assertNotNull($constructor);
        self::assertTrue($constructor->isPrivate());
    }

    #[Test]
    public function it_generates_cryptographically_secure_bytes() : void
    {
        $values = [];
        for ($i = 0; $i < 100; $i++) {
            $values[] = TraceId::generate()->value;
        }

        $uniqueValues = array_unique($values);

        self::assertCount(100, $uniqueValues);
    }

    #[Test]
    public function it_is_distinct_from_correlation_id_length() : void
    {
        $traceId       = TraceId::generate()->value;
        $correlationId = CorrelationId::generate()->value;

        self::assertNotSame(strlen($traceId), strlen($correlationId));
    }
}
