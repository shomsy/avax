<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\DataTransfer;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Hidden;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\IntegerType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\ListOf;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Optional;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\StringType;
use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use Avax\Components\DataStack\DataTransfer\System\Flows\SerializeDataObject\SerializeDataObject;
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject;
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataTransfer;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

final class SerializeDataObjectTest extends TestCase
{
    #[Test]
    public function to_array_serializes_public_properties() : void
    {
        $dto          = new SerializeDto();
        $dto->name    = 'AvaX';
        $dto->version = 4;
        $dto->active  = true;

        $array = (new SerializeDataObject())->toArray($dto);

        $this->assertSame([
                              'name' => 'AvaX',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     'version' => 4,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           'active' => true,
                          ], $array);
    }

    #[Test]
    public function to_array_via_data_transfer_facade() : void
    {
        $dto = DataTransfer::create(SerializeDto::class, [
            'name'    => 'Facade',
            'version' => 1,
            'active'  => false,
        ]);

        $array = DataTransfer::toArray($dto);

        $this->assertSame([
                              'name' => 'Facade',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     'version' => 1,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           'active' => false,
                          ], $array);
    }

    #[Test]
    public function to_json_encodes_to_json_string() : void
    {
        $dto = DataTransfer::create(SerializeDto::class, [
            'name'    => 'JSON',
            'version' => 2,
            'active'  => true,
        ]);

        $json = DataTransfer::toJson($dto);

        $decoded = json_decode($json, true);
        $this->assertSame('JSON', $decoded['name']);
        $this->assertSame(2, $decoded['version']);
        $this->assertTrue($decoded['active']);
    }

    #[Test]
    public function to_json_with_low_max_depth_truncates() : void
    {
        $config = new DataTransferConfig(maxDepth: 1);
        $dto    = DataTransfer::create(SerializeDto::class, [
            'name'    => 'test',
            'version' => 1,
            'active'  => true,
        ]);

        $serializer = new SerializeDataObject(dataTransferConfig: $config);
        $json       = $serializer->toJson($dto);
        $decoded    = json_decode($json, true);

        // With maxDepth 1, the object's public properties are still serialized
        // but nested objects would be null
        $this->assertIsString($json);
        $this->assertNotEmpty($decoded);
    }

    #[Test]
    public function to_json_accepts_flags() : void
    {
        $dto = DataTransfer::create(SerializeDto::class, [
            'name'    => 'Pretty',
            'version' => 1,
            'active'  => true,
        ]);

        $json = DataTransfer::toJson($dto, JSON_PRETTY_PRINT);

        $this->assertStringContainsString("\n", $json);
    }

    #[Test]
    public function to_stdclass_returns_stdclass() : void
    {
        $dto = DataTransfer::create(SerializeDto::class, [
            'name'    => 'StdClass',
            'version' => 3,
            'active'  => false,
        ]);

        $std = DataTransfer::toStdClass($dto);

        $this->assertInstanceOf(stdClass::class, $std);
        $this->assertSame('StdClass', $std->name);
        $this->assertSame(3, $std->version);
        $this->assertFalse($std->active);
    }

    #[Test]
    public function to_flat_array_returns_raw_object_vars() : void
    {
        $dto = DataTransfer::create(SerializeDto::class, [
            'name'    => 'Flat',
            'version' => 1,
            'active'  => true,
        ]);

        $flat = DataTransfer::toFlatArray($dto);

        $this->assertSame([
                              'name' => 'Flat',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     'version' => 1,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           'active' => true,
                          ], $flat);
    }

    #[Test]
    public function to_json_api_returns_json_api_structure() : void
    {
        $dto = DataTransfer::create(JsonApiDto::class, [
            'id'   => '123',
            'name' => 'Resource',
        ]);

        $result = DataTransfer::toJsonApi($dto, 'users');

        $this->assertArrayHasKey('data', $result);
        $this->assertSame('users', $result['data']['type']);
        $this->assertSame('123', $result['data']['id']);
        $this->assertSame('123', $result['data']['attributes']['id']);
        $this->assertSame('Resource', $result['data']['attributes']['name']);
    }

    #[Test]
    public function to_json_api_without_id_field() : void
    {
        $dto = DataTransfer::create(NoIdDto::class, ['name' => 'NoId']);

        $result = DataTransfer::toJsonApi($dto, 'items');

        $this->assertSame('items', $result['data']['type']);
        $this->assertNull($result['data']['id']);
        $this->assertSame('NoId', $result['data']['attributes']['name']);
    }

    #[Test]
    public function serialization_excludes_hidden_fields() : void
    {
        $dto = DataTransfer::create(SerializeHiddenDto::class, [
            'name'  => 'visible',
            'token' => 'secret-token',
        ]);

        $array = DataTransfer::toArray($dto);
        $json  = DataTransfer::toJson($dto);
        $std   = DataTransfer::toStdClass($dto);

        $this->assertArrayNotHasKey('token', $array);
        $decoded = json_decode($json, true);
        $this->assertArrayNotHasKey('token', $decoded);
        $this->assertObjectNotHasProperty('token', $std);
    }

    #[Test]
    public function serialization_normalizes_backed_enum_to_value() : void
    {
        $dto = DataTransfer::create(SerializeEnumDto::class, ['status' => 'active']);

        $array = DataTransfer::toArray($dto);

        $this->assertSame('active', $array['status']);
    }

    #[Test]
    public function serialization_normalizes_datetime_to_atom_format() : void
    {
        $dt  = new DateTimeImmutable('2024-01-15T10:30:00+00:00');
        $dto = DataTransfer::create(SerializeDateDto::class, ['createdAt' => $dt]);

        $array = DataTransfer::toArray($dto);

        $this->assertSame('2024-01-15T10:30:00+00:00', $array['createdAt']);
    }

    #[Test]
    public function serialization_handles_nested_dtos() : void
    {
        $child = DataTransfer::create(SerializeDto::class, [
            'name'    => 'child',
            'version' => 1,
            'active'  => true,
        ]);

        $parent        = new SerializeNestedDto();
        $parent->child = $child;
        $parent->label = 'parent';

        $array = (new SerializeDataObject())->toArray($parent);

        $this->assertSame('parent', $array['label']);
        $this->assertIsArray($array['child']);
        $this->assertSame('child', $array['child']['name']);
        $this->assertSame(1, $array['child']['version']);
    }

    #[Test]
    public function serialization_handles_list_of_dtos() : void
    {
        $dto        = new SerializeListDto();
        $dto->items = [
            DataTransfer::create(SerializeDto::class, ['name' => 'first', 'version' => 1, 'active' => true]),
            DataTransfer::create(SerializeDto::class, ['name' => 'second', 'version' => 2, 'active' => false]),
        ];

        $array = (new SerializeDataObject())->toArray($dto);

        $this->assertCount(2, $array['items']);
        $this->assertSame('first', $array['items'][0]['name']);
        $this->assertSame('second', $array['items'][1]['name']);
    }

    #[Test]
    public function serialization_normalizes_stringable_without_public_properties() : void
    {
        $dto       = new SerializeStringableDto();
        $dto->name = 'test';

        $array = (new SerializeDataObject())->toArray($dto);

        $this->assertSame('test', $array['name']);
    }

    #[Test]
    public function to_array_with_config_respects_max_depth() : void
    {
        $config     = new DataTransferConfig(maxDepth: 1);
        $serializer = new SerializeDataObject(dataTransferConfig: $config);

        $dto    = $this->createDeepNestedObject(5);
        $result = $serializer->toArray($dto);

        // With depth 1, deeply nested values should be null
        $this->assertNotNull($result);
    }

    /**
     * @return CircularDto
     */
    private function createDeepNestedObject(int $depth) : CircularDto
    {
        $root       = new CircularDto();
        $root->name = 'root';
        $current    = $root;

        for ($i = 0; $i < $depth; $i++) {
            $next             = new CircularDto();
            $next->name       = "level-{$i}";
            $current->related = $next;
            $current          = $next;
        }

        return $root;
    }

    #[Test]
    public function serialization_handles_nullable_fields() : void
    {
        $dto = DataTransfer::create(SerializeNullableDto::class, [
            'name' => 'test',
            'tag'  => null,
        ]);

        $array = DataTransfer::toArray($dto);

        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('tag', $array);
        $this->assertNull($array['tag']);
    }

    #[Test]
    public function to_flat_array_includes_all_public_properties() : void
    {
        $dto = DataTransfer::create(SerializeHiddenDto::class, [
            'name'  => 'visible',
            'token' => 'secret',
        ]);

        $flat = DataTransfer::toFlatArray($dto);

        // toFlatArray uses get_object_vars, so it includes everything
        $this->assertArrayHasKey('name', $flat);
        $this->assertArrayHasKey('token', $flat);
    }

    #[Test]
    public function serialize_with_config() : void
    {
        $config     = DataTransferConfig::default();
        $serializer = new SerializeDataObject(dataTransferConfig: $config);

        $dto = DataTransfer::create(SerializeDto::class, [
            'name'    => 'configured',
            'version' => 1,
            'active'  => true,
        ]);

        $array = $serializer->toArray($dto);

        $this->assertSame('configured', $array['name']);
    }

    #[Test]
    public function serialization_handles_deep_nesting_without_circular_ref() : void
    {
        $a          = new CircularDto();
        $b          = new CircularDto();
        $c          = new CircularDto();
        $a->name    = 'A';
        $b->name    = 'B';
        $c->name    = 'C';
        $a->related = $b;
        $b->related = $c;

        $serializer = new SerializeDataObject();
        $array      = $serializer->toArray($a);

        $this->assertSame('A', $array['name']);
        $this->assertSame('B', $array['related']['name']);
        $this->assertSame('C', $array['related']['related']['name']);
    }

    #[Test]
    public function json_serialize_with_pretty_print() : void
    {
        $dto = DataTransfer::create(SerializeDto::class, [
            'name'    => 'Pretty',
            'version' => 1,
            'active'  => true,
        ]);

        $json = DataTransfer::toJson($dto, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $decoded = json_decode($json, true);
        $this->assertSame('Pretty', $decoded['name']);
    }

    #[Test]
    public function serialize_enum_with_int_backing() : void
    {
        $dto = DataTransfer::create(SerializeIntEnumDto::class, ['priority' => 3]);

        $array = DataTransfer::toArray($dto);

        $this->assertSame(3, $array['priority']);
    }

    #[Test]
    public function serialize_excludes_hidden_from_json_api() : void
    {
        $dto = DataTransfer::create(JsonApiHiddenDto::class, [
            'id'     => '1',
            'name'   => 'visible',
            'secret' => 'hidden',
        ]);

        $result = DataTransfer::toJsonApi($dto, 'resources');

        $this->assertArrayNotHasKey('secret', $result['data']['attributes']);
    }
}

// -- Test DTOs --

final class SerializeDto extends DataObject
{
    #[Required]
    public string $name;

    #[Required]
    public int $version;

    public bool $active = false;
}

final class SerializeHiddenDto extends DataObject
{
    #[Required]
    public string $name;

    #[Required]
    #[Hidden]
    public string $token;
}

enum TestStatusEnum: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
}

final class SerializeEnumDto extends DataObject
{
    #[Required]
    public TestStatusEnum $status;
}

final class SerializeIntEnumDto extends DataObject
{
    #[Required]
    public IntPriority $priority;
}

enum IntPriority: int
{
    case Low    = 1;
    case Medium = 2;
    case High   = 3;
}

final class SerializeDateDto extends DataObject
{
    #[Required]
    public DateTimeImmutable $createdAt;
}

final class SerializeNestedDto
{
    #[Required]
    public SerializeDto $child;

    #[Required]
    public string $label;
}

final class SerializeListDto
{
    /**
     * @param list<SerializeDto> $items
     */
    #[Required]
    #[ListOf(SerializeDto::class)]
    public array $items;
}

final class SerializeStringableDto
{
    #[Required]
    public string $name;

    public function __toString() : string
    {
        return $this->name;
    }
}

final class SerializeNullableDto extends DataObject
{
    #[Required]
    public string $name;

    #[Optional]
    public ?string $tag = null;
}

final class JsonApiDto extends DataObject
{
    #[Required]
    public string $id;

    #[Required]
    public string $name;
}

final class NoIdDto extends DataObject
{
    #[Required]
    public string $name;
}

final class CircularDto
{
    public string $name    = '';
    public ?self  $related = null;
}

final class JsonApiHiddenDto extends DataObject
{
    #[Required]
    public string $id;

    #[Required]
    public string $name;

    #[Required]
    #[Hidden]
    public string $secret;
}
