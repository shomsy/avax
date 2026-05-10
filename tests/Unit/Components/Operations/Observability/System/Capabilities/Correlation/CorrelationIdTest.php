<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Observability\System\Capabilities\Correlation;

use Avax\Components\Operations\Observability\System\Capabilities\Correlation\CorrelationId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Stringable;

final class CorrelationIdTest extends TestCase
{
    #[Test]
    public function it_generates_unique_ids() : void
    {
        $id1 = CorrelationId::generate();
        $id2 = CorrelationId::generate();

        self::assertNotSame($id1->value, $id2->value);
    }

    #[Test]
    public function it_generates_hexadecimal_value() : void
    {
        $id = CorrelationId::generate();

        self::assertMatchesRegularExpression('/^[0-9a-f]+$/', $id->value);
    }

    #[Test]
    public function it_generates_24_character_hex_string() : void
    {
        $id = CorrelationId::generate();

        self::assertSame(24, strlen($id->value));
    }

    #[Test]
    public function it_creates_from_string() : void
    {
        $id = CorrelationId::fromString('my-correlation-id');

        self::assertSame('my-correlation-id', $id->value);
    }

    #[Test]
    public function it_creates_from_empty_string() : void
    {
        $id = CorrelationId::fromString('');

        self::assertSame('', $id->value);
    }

    #[Test]
    public function it_converts_to_string() : void
    {
        $id = CorrelationId::fromString('test-id');

        self::assertSame('test-id', (string) $id);
    }

    #[Test]
    public function it_implements_stringable() : void
    {
        $id = CorrelationId::generate();

        self::assertInstanceOf(Stringable::class, $id);
    }

    #[Test]
    public function it_is_readonly() : void
    {
        $reflection = new ReflectionClass(CorrelationId::class);

        self::assertTrue($reflection->isReadonly());
    }

    #[Test]
    public function it_is_final() : void
    {
        $reflection = new ReflectionClass(CorrelationId::class);

        self::assertTrue($reflection->isFinal());
    }

    #[Test]
    public function it_has_private_constructor() : void
    {
        $reflection  = new ReflectionClass(CorrelationId::class);
        $constructor = $reflection->getConstructor();

        self::assertNotNull($constructor);
        self::assertTrue($constructor->isPrivate());
    }

    #[Test]
    public function it_preserves_value_across_to_string() : void
    {
        $original = 'custom-correlation-123';
        $id       = CorrelationId::fromString($original);

        self::assertSame($original, (string) $id);
        self::assertSame($original, $id->value);
    }

    #[Test]
    public function it_generates_cryptographically_secure_bytes() : void
    {
        $values = [];
        for ($i = 0; $i < 100; $i++) {
            $values[] = CorrelationId::generate()->value;
        }

        $uniqueValues = array_unique($values);

        self::assertCount(100, $uniqueValues);
    }
}
