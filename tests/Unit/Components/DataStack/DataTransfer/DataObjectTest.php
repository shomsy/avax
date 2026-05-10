<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\DataTransfer;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Hidden;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\IntegerType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\StringType;
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject;
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataTransfer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use stdClass;

final class DataObjectTest extends TestCase
{
    #[Test]
    public function data_object_is_abstract() : void
    {
        $reflection = new ReflectionClass(DataObject::class);

        $this->assertTrue($reflection->isAbstract());
    }

    #[Test]
    public function to_array_delegates_to_data_transfer() : void
    {
        $dto          = new ConcreteDataObject();
        $dto->name    = 'AvaX';
        $dto->version = 2;

        $array = $dto->toArray();

        $this->assertSame(['name' => 'AvaX', 'version' => 2], $array);
    }

    #[Test]
    public function to_json_delegates_to_data_transfer() : void
    {
        $dto          = new ConcreteDataObject();
        $dto->name    = 'AvaX';
        $dto->version = 2;

        $json = $dto->toJson();

        $this->assertJsonStringEqualsJsonString('{"name":"AvaX","version":2}', $json);
    }

    #[Test]
    public function to_json_passes_flags() : void
    {
        $dto          = new ConcreteDataObject();
        $dto->name    = 'AvaX';
        $dto->version = 2;

        $json = $dto->toJson(JSON_PRETTY_PRINT);

        $this->assertStringContainsString("\n", $json);
    }

    #[Test]
    public function to_stdclass_delegates_to_data_transfer() : void
    {
        $dto          = new ConcreteDataObject();
        $dto->name    = 'AvaX';
        $dto->version = 2;

        $std = $dto->toStdClass();

        $this->assertInstanceOf(stdClass::class, $std);
        $this->assertSame('AvaX', $std->name);
        $this->assertSame(2, $std->version);
    }

    #[Test]
    public function data_object_subclass_can_be_created_via_data_transfer() : void
    {
        $dto = DataTransfer::create(ConcreteDataObject::class, [
            'name'    => 'test',
            'version' => 5,
        ]);

        $this->assertInstanceOf(DataObject::class, $dto);
        $this->assertInstanceOf(ConcreteDataObject::class, $dto);
        $this->assertSame('test', $dto->name);
        $this->assertSame(5, $dto->version);
    }

    #[Test]
    public function to_array_excludes_hidden_fields() : void
    {
        $dto         = new HiddenDataObject();
        $dto->name   = 'public';
        $dto->secret = 'hidden-value';

        $array = $dto->toArray();

        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('secret', $array);
    }

    #[Test]
    public function to_json_excludes_hidden_fields() : void
    {
        $dto         = new HiddenDataObject();
        $dto->name   = 'public';
        $dto->secret = 'hidden-value';

        $json = $dto->toJson();
        $data = json_decode($json, true);

        $this->assertArrayHasKey('name', $data);
        $this->assertArrayNotHasKey('secret', $data);
    }

    #[Test]
    public function to_stdclass_excludes_hidden_fields() : void
    {
        $dto         = new HiddenDataObject();
        $dto->name   = 'public';
        $dto->secret = 'hidden-value';

        $std = $dto->toStdClass();

        $this->assertObjectHasProperty('name', $std);
        $this->assertObjectNotHasProperty('secret', $std);
    }

    #[Test]
    public function data_object_has_no_constructor_by_default() : void
    {
        $reflection  = new ReflectionClass(DataObject::class);
        $constructor = $reflection->getConstructor();

        $this->assertNull($constructor);
    }

    #[Test]
    public function subclass_with_no_constructor_properties_are_hydrated() : void
    {
        $dto = DataTransfer::create(MinimalDataObject::class, [
            'value' => 'hello',
        ]);

        $this->assertSame('hello', $dto->value);
    }

    #[Test]
    public function subclass_can_have_constructor_and_still_work() : void
    {
        $dto = DataTransfer::create(ConstructingDataObject::class, [
            'name' => 'constructed',
        ]);

        $this->assertSame('constructed', $dto->name);
    }

    #[Test]
    public function data_object_does_not_call_container() : void
    {
        // DataObject is a pure transfer object — no container coupling
        $dto          = new ConcreteDataObject();
        $dto->name    = 'test';
        $dto->version = 1;

        // Verify it serializes without any container involvement
        $array = $dto->toArray();
        $this->assertIsArray($array);
        $this->assertNotEmpty($array);
    }

    #[Test]
    public function data_object_does_not_know_about_http() : void
    {
        $reflection = new ReflectionClass(DataObject::class);
        $methods    = array_map(static fn (ReflectionMethod $m) => $m->getName(), $reflection->getMethods());

        // No HTTP-specific methods
        $this->assertNotContains('getRequest', $methods);
        $this->assertNotContains('getResponse', $methods);
        $this->assertNotContains('handle', $methods);
    }

    #[Test]
    public function data_object_to_array_returns_only_visible_fields() : void
    {
        $dto = DataTransfer::create(FullFeaturedDataObject::class, [
            'id'     => 1,
            'name'   => 'test',
            'secret' => 'hidden',
            'extra'  => 'visible',
        ]);

        $array = $dto->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('extra', $array);
        $this->assertArrayNotHasKey('secret', $array);
    }
}

// -- Test DTOs --

final class ConcreteDataObject extends DataObject
{
    #[Required]
    #[StringType]
    public string $name;

    #[Required]
    #[IntegerType]
    public int $version;
}

final class HiddenDataObject extends DataObject
{
    #[Required]
    public string $name;

    #[Hidden]
    public string $secret;
}

final class MinimalDataObject extends DataObject
{
    #[Required]
    public string $value;
}

final class ConstructingDataObject extends DataObject
{
    public function __construct(
        #[Required]
        public string $name,
    ) {}
}

final class FullFeaturedDataObject extends DataObject
{
    #[Required]
    public int $id;

    #[Required]
    public string $name;

    #[Required]
    #[Hidden]
    public string $secret;

    public string $extra = 'default';
}
