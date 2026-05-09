<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data\Flows;

use Avax\Components\DataStack\Data\System\Flows\SerializeStructure\SerializeStructure;
use PHPUnit\Framework\TestCase;

final class SerializeStructureTest extends TestCase
{
    private SerializeStructure $serializer;

    public function test_to_array_passthrough_scalar() : void
    {
        self::assertSame(42, $this->serializer->toArray(42));
        self::assertSame('hello', $this->serializer->toArray('hello'));
        self::assertSame(3.14, $this->serializer->toArray(3.14));
        self::assertTrue($this->serializer->toArray(true));
        self::assertNull($this->serializer->toArray(null));
    }

    public function test_to_array_passthrough_array() : void
    {
        self::assertSame(['a', 'b'], $this->serializer->toArray(['a', 'b']));
    }

    public function test_to_array_calls_to_array_on_object() : void
    {
        $obj = new class {
            /** @return array{name: string} */
            public function toArray() : array
            {
                return ['name' => 'test'];
            }
        };
        self::assertSame(['name' => 'test'], $this->serializer->toArray($obj));
    }

    public function test_to_array_calls_serialize_on_object() : void
    {
        $obj = new class {
            public function __serialize() : array
            {
                return ['data' => 123];
            }
        };
        self::assertSame(['data' => 123], $this->serializer->toArray($obj));
    }

    public function test_to_json() : void
    {
        self::assertSame('{"name":"test"}', $this->serializer->toJson(['name' => 'test']));
    }

    public function test_to_json_with_flags() : void
    {
        $json = $this->serializer->toJson(['name' => 'test'], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        self::assertStringContainsString('"name"', $json);
    }

    public function test_is_serializable() : void
    {
        self::assertTrue($this->serializer->isSerializable(42));
        self::assertTrue($this->serializer->isSerializable(null));
        self::assertTrue($this->serializer->isSerializable(['a']));
        self::assertTrue($this->serializer->isSerializable(new class {
            /** @return array<empty> */
            public function toArray() : array { return []; }
        }));
        // @phpstan-ignore-next-line
        self::assertFalse($this->serializer->isSerializable(new class {}));
    }

    protected function setUp() : void
    {
        $this->serializer = new SerializeStructure();
    }
}
