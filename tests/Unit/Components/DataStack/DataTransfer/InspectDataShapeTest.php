<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\DataTransfer;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CastWith;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\DefaultValue;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Hidden;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\ListOf;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\MapFrom;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Optional;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\StringType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\CacheDataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataField;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataFieldType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\InspectDataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\ReadClassDataShape;
use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InspectDataShapeTest extends TestCase
{
    #[Test]
    public function inspect_returns_data_shape_for_class() : void
    {
        $shape = (new InspectDataShape())->inspect(SimpleShapeDto::class);

        $this->assertInstanceOf(DataShape::class, $shape);
        $this->assertSame(SimpleShapeDto::class, $shape->class);
    }

    #[Test]
    public function data_shape_contains_fields() : void
    {
        $shape = (new InspectDataShape())->inspect(SimpleShapeDto::class);

        $fields = $shape->fields();
        $this->assertArrayHasKey('name', $fields);
        $this->assertArrayHasKey('email', $fields);
    }

    #[Test]
    public function data_field_has_correct_name() : void
    {
        $shape = (new InspectDataShape())->inspect(SimpleShapeDto::class);

        $field = $shape->field('name');
        $this->assertNotNull($field);
        $this->assertSame('name', $field->name);
    }

    #[Test]
    public function data_field_knows_input_name() : void
    {
        $shape = (new InspectDataShape())->inspect(MappedShapeDto::class);

        $field = $shape->field('displayName');
        $this->assertNotNull($field);
        $this->assertSame('full_name', $field->inputName);
    }

    #[Test]
    public function data_field_knows_if_required() : void
    {
        $shape = (new InspectDataShape())->inspect(SimpleShapeDto::class);

        $requiredField = $shape->field('name');
        $this->assertNotNull($requiredField);
        $this->assertTrue($requiredField->isRequired());

        $optionalField = $shape->field('email');
        $this->assertNotNull($optionalField);
        $this->assertFalse($optionalField->isRequired());
    }

    #[Test]
    public function data_field_knows_if_hidden() : void
    {
        $shape = (new InspectDataShape())->inspect(HiddenShapeDto::class);

        $hiddenField = $shape->field('secret');
        $this->assertNotNull($hiddenField);
        $this->assertTrue($hiddenField->isHidden());

        $visibleField = $shape->field('name');
        $this->assertNotNull($visibleField);
        $this->assertFalse($visibleField->isHidden());
    }

    #[Test]
    public function data_field_has_attributes() : void
    {
        $shape = (new InspectDataShape())->inspect(SimpleShapeDto::class);

        $field = $shape->field('name');
        $this->assertNotNull($field);
        $this->assertNotEmpty($field->attributes);
        $this->assertTrue($field->hasAttribute(Required::class));
        $this->assertTrue($field->hasAttribute(StringType::class));
    }

    #[Test]
    public function data_field_type_is_correct() : void
    {
        $shape = (new InspectDataShape())->inspect(TypedShapeDto::class);

        $stringField = $shape->field('name');
        $this->assertNotNull($stringField);
        $this->assertSame('string', $stringField->dataFieldType->primaryName());
        $this->assertTrue($stringField->dataFieldType->isScalar());

        $intField = $shape->field('count');
        $this->assertNotNull($intField);
        $this->assertSame('int', $intField->dataFieldType->primaryName());
    }

    #[Test]
    public function data_field_type_allows_null() : void
    {
        $shape = (new InspectDataShape())->inspect(NullableShapeDto::class);

        $field = $shape->field('tag');
        $this->assertNotNull($field);
        $this->assertTrue($field->dataFieldType->allowsNull);
    }

    #[Test]
    public function data_field_is_constructor_field_for_promoted_property() : void
    {
        $shape = (new InspectDataShape())->inspect(ConstructorShapeDto::class);

        $field = $shape->field('name');
        $this->assertNotNull($field);
        $this->assertTrue($field->isConstructorField);
        $this->assertTrue($field->isPromotedProperty);
    }

    #[Test]
    public function data_field_is_public_property_for_non_constructor_property() : void
    {
        $shape = (new InspectDataShape())->inspect(PropertyShapeDto::class);

        $field = $shape->field('name');
        $this->assertNotNull($field);
        $this->assertTrue($field->isPublicProperty);
        $this->assertFalse($field->isConstructorField);
    }

    #[Test]
    public function data_field_has_default_value() : void
    {
        $shape = (new InspectDataShape())->inspect(DefaultShapeDto::class);

        $field = $shape->field('role');
        $this->assertNotNull($field);
        $this->assertTrue($field->hasDefaultAttribute());
        $this->assertSame('user', $field->defaultFromAttribute());
    }

    #[Test]
    public function data_field_list_item_class() : void
    {
        $shape = (new InspectDataShape())->inspect(ListShapeDto::class);

        $field = $shape->field('items');
        $this->assertNotNull($field);
        $this->assertSame(SimpleShapeDto::class, $field->listItemClass());
    }

    #[Test]
    public function constructor_fields_filters_only_constructor_fields() : void
    {
        $shape = (new InspectDataShape())->inspect(MixedShapeDto::class);

        $constructorFields = $shape->constructorFields();
        foreach ($constructorFields as $field) {
            $this->assertTrue($field->isConstructorField);
        }
    }

    #[Test]
    public function public_property_fields_filters_only_public_properties() : void
    {
        $shape = (new InspectDataShape())->inspect(MixedShapeDto::class);

        $publicFields = $shape->publicPropertyFields();
        foreach ($publicFields as $field) {
            $this->assertTrue($field->isPublicProperty);
        }
    }

    #[Test]
    public function input_names_returns_all_input_names() : void
    {
        $shape = (new InspectDataShape())->inspect(MappedShapeDto::class);

        $names = $shape->inputNames();
        $this->assertContains('full_name', $names);
    }

    #[Test]
    public function field_returns_null_for_nonexistent_field() : void
    {
        $shape = (new InspectDataShape())->inspect(SimpleShapeDto::class);

        $this->assertNull($shape->field('nonexistent'));
    }

    #[Test]
    public function inspect_caches_results() : void
    {
        $inspector = new InspectDataShape();

        $shape1 = $inspector->inspect(CacheableDto::class);
        $shape2 = $inspector->inspect(CacheableDto::class);

        $this->assertSame($shape1, $shape2);
    }

    #[Test]
    public function cache_can_be_cleared() : void
    {
        $cache = new CacheDataShape();

        $shape1 = (new InspectDataShape(cacheDataShape: $cache))->inspect(ClearableDto::class);
        $cache->clear();
        $shape2 = (new InspectDataShape(cacheDataShape: $cache))->inspect(ClearableDto::class);

        // After clear, a new shape is built
        $this->assertNotSame($shape1, $shape2);
    }

    #[Test]
    public function read_class_data_shape_reads_both_constructor_and_public_fields() : void
    {
        $reader = new ReadClassDataShape();
        $shape  = $reader->read(MixedShapeDto::class, DataTransferConfig::default());

        $this->assertSame(MixedShapeDto::class, $shape->class);
        $fields = $shape->fields();
        $this->assertArrayHasKey('name', $fields);   // constructor
        $this->assertArrayHasKey('extra', $fields);  // public property
    }

    #[Test]
    public function data_field_type_names() : void
    {
        $type = new DataFieldType(['string', 'null'], allowsNull: true);

        $this->assertSame(['string', 'null'], $type->names());
        $this->assertSame('string', $type->primaryName());
        $this->assertSame('string|null', $type->displayName());
    }

    #[Test]
    public function data_field_type_is_mixed() : void
    {
        $type = DataFieldType::mixed();

        $this->assertTrue($type->isMixed());
        $this->assertSame(['mixed'], $type->names());
    }

    #[Test]
    public function data_field_type_is_array() : void
    {
        $type = new DataFieldType(['array']);

        $this->assertTrue($type->isArray());
        $this->assertFalse($type->isScalar());
    }

    #[Test]
    public function data_field_type_is_class() : void
    {
        $type = new DataFieldType([SimpleShapeDto::class]);

        $this->assertTrue($type->isClass());
        $this->assertFalse($type->isScalar());
        $this->assertFalse($type->isMixed());
    }

    #[Test]
    public function data_field_type_is_backed_enum() : void
    {
        $type = new DataFieldType([TestShapeStatus::class]);

        $this->assertTrue($type->isBackedEnum());
        // Note: class_exists() also returns true for enums in PHP
    }

    #[Test]
    public function inspect_with_custom_config() : void
    {
        $config    = DataTransferConfig::default();
        $inspector = new InspectDataShape(dataTransferConfig: $config);

        $shape = $inspector->inspect(SimpleShapeDto::class);

        $this->assertInstanceOf(DataShape::class, $shape);
    }

    #[Test]
    public function data_field_caster_class() : void
    {
        $shape = (new InspectDataShape())->inspect(CastShapeDto::class);

        $field = $shape->field('value');
        $this->assertNotNull($field);
        $this->assertSame(TestCaster::class, $field->casterClass());
    }

    #[Test]
    public function shape_handles_class_without_constructor() : void
    {
        $shape = (new InspectDataShape())->inspect(NoConstructorShapeDto::class);

        $this->assertSame(NoConstructorShapeDto::class, $shape->class);
        $this->assertArrayHasKey('value', $shape->fields());
    }

    #[Test]
    public function data_field_type_from_null_type() : void
    {
        $type = DataFieldType::fromReflectionType(null);

        $this->assertTrue($type->isMixed());
        $this->assertTrue($type->allowsNull);
    }

    #[Test]
    public function inspect_handles_nested_dto_field() : void
    {
        $shape = (new InspectDataShape())->inspect(NestedShapeDto::class);

        $field = $shape->field('child');
        $this->assertNotNull($field);
        $this->assertTrue($field->dataFieldType->isClass());
    }

    protected function tearDown() : void
    {
        (new CacheDataShape())->clear();
    }
}

// -- Test DTOs --

final class SimpleShapeDto
{
    public function __construct(
        #[Required]
        #[StringType]
        public string  $name,
        #[Optional]
        public ?string $email = null,
    ) {}
}

final class MappedShapeDto
{
    public function __construct(
        #[Required]
        #[MapFrom('full_name')]
        public string $displayName,
    ) {}
}

final class HiddenShapeDto
{
    #[Required]
    public string $name;

    #[Required]
    #[Hidden]
    public string $secret;
}

final class TypedShapeDto
{
    public function __construct(
        public string $name,
        public int    $count,
    ) {}
}

final class NullableShapeDto
{
    public function __construct(
        public string  $name,
        public ?string $tag = null,
    ) {}
}

final class ConstructorShapeDto
{
    public function __construct(
        public string $name,
    ) {}
}

final class PropertyShapeDto
{
    public string $name;
}

final class DefaultShapeDto
{
    public function __construct(
        #[DefaultValue('user')]
        public string $role = 'user',
    ) {}
}

final class ListShapeDto
{
    /**
     * @param list<SimpleShapeDto> $items
     */
    public function __construct(
        #[Required]
        #[ListOf(SimpleShapeDto::class)]
        public array $items,
    ) {}
}

final class MixedShapeDto
{
    public string $extra = '';

    public function __construct(
        public string $name,
    ) {}
}

final class CacheableDto
{
    public string $value = '';
}

final class ClearableDto
{
    public string $value = '';
}

enum TestShapeStatus: string
{
    case On  = 'on';
    case Off = 'off';
}

final class TestCaster
{
    public function cast(mixed $value, string $field) : mixed
    {
        return strtolower((string) $value);
    }
}

final class CastShapeDto
{
    public function __construct(
        #[CastWith(TestCaster::class)]
        public string $value,
    ) {}
}

final class NoConstructorShapeDto
{
    public string $value = 'default';
}

final class NestedShapeDto
{
    public function __construct(
        public SimpleShapeDto $child,
    ) {}
}
