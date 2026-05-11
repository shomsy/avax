<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\DataTransfer;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CastWith;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\DefaultValue;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Email;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\IntegerType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\ListOf;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\MapFrom;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Optional;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\StringType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferFailure;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferViolation;
use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use Avax\Components\DataStack\DataTransfer\System\Flows\CreateDataObject\CreateDataObject;
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;
use ValueError;

final class CreateDataObjectTest extends TestCase
{
    #[Test]
    public function creates_object_from_array_with_constructor_promoted_properties() : void
    {
        $dto = (new CreateDataObject())->create(ConstructorPromotedDto::class, [
            'name'    => 'AvaX',
            'version' => 4,
        ]);

        $this->assertInstanceOf(ConstructorPromotedDto::class, $dto);
        $this->assertSame('AvaX', $dto->name);
        $this->assertSame(4, $dto->version);
    }

    #[Test]
    public function creates_object_from_array_with_no_constructor_properties() : void
    {
        // CDONoConstructorDto uses #[MapFrom('full_name')] on $name
        $dto = (new CreateDataObject())->create(CDONoConstructorDto::class, [
            'full_name' => 'AvaX',
            'count'     => 10,
        ]);

        $this->assertInstanceOf(CDONoConstructorDto::class, $dto);
        $this->assertSame('AvaX', $dto->name);
        $this->assertSame(10, $dto->count);
    }

    #[Test]
    public function creates_object_from_stdclass_input() : void
    {
        // CDONoConstructorDto uses #[MapFrom('full_name')] on $name
        $input            = new stdClass();
        $input->full_name = 'AvaX';
        $input->count     = 7;

        $dto = (new CreateDataObject())->create(CDONoConstructorDto::class, $input);

        $this->assertSame('AvaX', $dto->name);
        $this->assertSame(7, $dto->count);
    }

    #[Test]
    public function throws_when_required_field_is_missing() : void
    {
        $this->expectException(DataTransferFailure::class);
        $this->expectExceptionMessage('Data validation failed.');

        (new CreateDataObject())->create(ConstructorPromotedDto::class, ['version' => 1]);
    }

    #[Test]
    public function violation_message_includes_field_name_for_required() : void
    {
        try {
            (new CreateDataObject())->create(ConstructorPromotedDto::class, ['version' => 1]);
            $this->fail('Expected DataTransferFailure');
        } catch (DataTransferFailure $e) {
            $this->assertNotNull($e->violations);
            $found = false;
            foreach ($e->violations as $v) {
                if ($v->field === 'name' && str_contains($v->message, 'required')) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Expected required violation for "name" field');
        }
    }

    #[Test]
    public function optional_field_is_skipped_when_missing() : void
    {
        $dto = (new CreateDataObject())->create(OptionalDto::class, ['name' => 'test']);

        $this->assertSame('test', $dto->name);
        // Optional field without a value should not be set — property remains uninitialized or default
        $this->assertFalse(property_exists($dto, 'tag') && isset($dto->tag));
    }

    #[Test]
    public function default_value_is_applied_when_field_missing() : void
    {
        $dto = (new CreateDataObject())->create(CDODefaultDto::class, ['name' => 'test']);

        $this->assertSame('fallback', $dto->role);
    }

    #[Test]
    public function default_value_is_overridden_by_input() : void
    {
        $dto = (new CreateDataObject())->create(CDODefaultDto::class, [
            'name' => 'test',
            'role' => 'admin',
        ]);

        $this->assertSame('admin', $dto->role);
    }

    #[Test]
    public function validates_type_mismatch_for_string() : void
    {
        try {
            (new CreateDataObject())->create(CreateDataObjectTypedDto::class, ['name' => 12345, 'count' => 1]);
            $this->fail('Expected DataTransferFailure');
        } catch (DataTransferFailure $e) {
            $this->assertNotNull($e->violations);
            $found = false;
            foreach ($e->violations as $v) {
                if (str_contains($v->message, 'must be of type string')) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Expected type violation for string field');
        }
    }

    #[Test]
    public function validates_type_mismatch_for_int() : void
    {
        try {
            (new CreateDataObject())->create(CreateDataObjectTypedDto::class, ['name' => 'ok', 'count' => 'not-int']);
            $this->fail('Expected DataTransferFailure');
        } catch (DataTransferFailure $e) {
            $this->assertNotNull($e->violations);
            $found = false;
            foreach ($e->violations as $v) {
                if (str_contains($v->message, 'must be of type int')) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Expected type violation for int field');
        }
    }

    #[Test]
    public function int_is_accepted_for_float_type() : void
    {
        $dto = (new CreateDataObject())->create(FloatAcceptDto::class, ['amount' => 42]);

        // PHP coerces int to float for typed float property
        $this->assertEquals(42.0, $dto->amount);
    }

    #[Test]
    public function float_is_rejected_for_int_type() : void
    {
        try {
            (new CreateDataObject())->create(CreateDataObjectTypedDto::class, ['name' => 'ok', 'count' => 3.14]);
            $this->fail('Expected DataTransferFailure');
        } catch (DataTransferFailure $e) {
            $this->assertNotNull($e->violations);
            $found = false;
            foreach ($e->violations as $v) {
                if (str_contains($v->message, 'must be of type int')) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Expected type violation: float not accepted for int');
        }
    }

    #[Test]
    public function validates_email_attribute() : void
    {
        try {
            (new CreateDataObject())->create(CDOEmailDto::class, ['email' => 'not-an-email']);
            $this->fail('Expected DataTransferFailure');
        } catch (DataTransferFailure $e) {
            $this->assertNotNull($e->violations);
            $found = false;
            foreach ($e->violations as $v) {
                if (str_contains($v->message, 'valid email')) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Expected email validation violation');
        }
    }

    #[Test]
    public function accepts_valid_email() : void
    {
        $dto = (new CreateDataObject())->create(CDOEmailDto::class, ['email' => 'user@example.com']);

        $this->assertSame('user@example.com', $dto->email);
    }

    #[Test]
    public function casts_backed_enum_from_string() : void
    {
        $dto = (new CreateDataObject())->create(CDOEnumDto::class, ['status' => 'active']);

        $this->assertSame(TestStatus::Active, $dto->status);
    }

    #[Test]
    public function throws_on_invalid_backed_enum_value() : void
    {
        $this->expectException(ValueError::class);

        (new CreateDataObject())->create(CDOEnumDto::class, ['status' => 'unknown']);
    }

    #[Test]
    public function casts_nested_dto_from_array() : void
    {
        $dto = (new CreateDataObject())->create(CDONestedDto::class, [
            'child' => ['name' => 'child-name', 'version' => 2],
        ]);

        $this->assertInstanceOf(ConstructorPromotedDto::class, $dto->child);
        $this->assertSame('child-name', $dto->child->name);
        $this->assertSame(2, $dto->child->version);
    }

    #[Test]
    public function passes_through_existing_nested_object() : void
    {
        $child = new ConstructorPromotedDto('existing', 99);

        $dto = (new CreateDataObject())->create(CDONestedDto::class, ['child' => $child]);

        $this->assertSame($child, $dto->child);
    }

    #[Test]
    public function casts_list_of_nested_dtos() : void
    {
        $dto = (new CreateDataObject())->create(CDOListDto::class, [
            'items' => [
                ['name' => 'first', 'version' => 1],
                ['name' => 'second', 'version' => 2],
            ],
        ]);

        $this->assertCount(2, $dto->items);
        $this->assertInstanceOf(ConstructorPromotedDto::class, $dto->items[0]);
        $this->assertInstanceOf(ConstructorPromotedDto::class, $dto->items[1]);
        $this->assertSame('first', $dto->items[0]->name);
        $this->assertSame('second', $dto->items[1]->name);
    }

    #[Test]
    public function list_casting_fails_when_value_is_not_array() : void
    {
        try {
            (new CreateDataObject())->create(CDOListDto::class, ['items' => 'not-array']);
            $this->fail('Expected DataTransferFailure');
        } catch (DataTransferFailure $e) {
            $this->assertNotNull($e->violations);
            $found = false;
            foreach ($e->violations as $v) {
                if (str_contains($v->message, 'must be an array for list casting')) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Expected list casting violation');
        }
    }

    #[Test]
    public function list_casting_fails_for_invalid_item() : void
    {
        try {
            (new CreateDataObject())->create(CDOListDto::class, [
                'items' => [
                    ['name' => 'ok', 'version' => 1],
                    'not-an-array',
                ],
            ]);
            $this->fail('Expected DataTransferFailure');
        } catch (DataTransferFailure $e) {
            $this->assertNotNull($e->violations);
            $found = false;
            foreach ($e->violations as $v) {
                if (str_contains($v->message, 'is not a valid')) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Expected invalid list item violation');
        }
    }

    #[Test]
    public function list_casting_passes_through_existing_objects() : void
    {
        $item = new ConstructorPromotedDto('existing', 42);

        $dto = (new CreateDataObject())->create(CDOListDto::class, [
            'items' => [$item],
        ]);

        $this->assertSame($item, $dto->items[0]);
    }

    #[Test]
    public function applies_custom_caster_with_cast_method() : void
    {
        $dto = (new CreateDataObject())->create(CDOCastDto::class, ['value' => 'HELLO WORLD']);

        $this->assertSame('hello world', $dto->value);
    }

    #[Test]
    public function resolves_map_from_attribute() : void
    {
        $dto = (new CreateDataObject())->create(CDOMappedDto::class, ['full_name' => 'AvaX']);

        $this->assertSame('AvaX', $dto->name);
    }

    #[Test]
    public function collects_multiple_violations() : void
    {
        try {
            (new CreateDataObject())->create(MultiRequiredDto::class, []);
            $this->fail('Expected DataTransferFailure');
        } catch (DataTransferFailure $e) {
            $this->assertNotNull($e->violations);
            $this->assertGreaterThanOrEqual(2, $e->violations->count());
        }
    }

    #[Test]
    public function null_value_skips_hydration_and_type_validation() : void
    {
        // When value is null, hydrateValue is not called, so no type violation for nullable
        $dto = (new CreateDataObject())->create(NullableDto::class, ['name' => 'test', 'tag' => null]);

        $this->assertSame('test', $dto->name);
        $this->assertNull($dto->tag);
    }

    #[Test]
    public function uses_config_from_constructor() : void
    {
        $config = DataTransferConfig::default();
        $flow   = new CreateDataObject(dataTransferConfig: $config);

        $dto = $flow->create(ConstructorPromotedDto::class, ['name' => 'AvaX', 'version' => 1]);

        $this->assertSame('AvaX', $dto->name);
    }

    #[Test]
    public function hydrate_into_hydrates_existing_object() : void
    {
        $dto        = new CDONoConstructorDto();
        $dto->name  = 'original';
        $dto->count = 0;

        $flow = new CreateDataObject();
        // CDONoConstructorDto uses #[MapFrom('full_name')] on $name, #[Required]
        $violations = $flow->hydrateInto($dto, ['full_name' => 'updated', 'count' => 42]);

        $this->assertSame([], $violations);
        $this->assertSame('updated', $dto->name);
        $this->assertSame(42, $dto->count);
    }

    #[Test]
    public function hydrate_into_returns_violations_for_required() : void
    {
        $dto = new CDONoConstructorDto();

        $flow       = new CreateDataObject();
        $violations = $flow->hydrateInto($dto, []);

        $this->assertNotEmpty($violations);
        $this->assertInstanceOf(DataTransferViolation::class, $violations[0]);
    }

    #[Test]
    public function hydrate_into_does_not_set_value_on_violation() : void
    {
        $dto        = new CDONoConstructorDto();
        $dto->name  = 'original';
        $dto->count = 0;

        $flow = new CreateDataObject();
        // Missing required 'name' should create violation and NOT overwrite
        $violations = $flow->hydrateInto($dto, ['count' => 99]);

        $this->assertNotEmpty($violations);
        // name should remain original because it had a violation
        $this->assertSame('original', $dto->name);
        // count should be updated since no violation
        $this->assertSame(99, $dto->count);
    }

    #[Test]
    public function hydrate_into_skips_static_properties() : void
    {
        $dto                           = new StaticPropertyDto();
        StaticPropertyDto::$staticName = 'static-original';

        $flow = new CreateDataObject();
        $flow->hydrateInto($dto, ['staticName' => 'new-static', 'name' => 'instance-name']);

        $this->assertSame('static-original', StaticPropertyDto::$staticName);
        $this->assertSame('instance-name', $dto->name);
    }

    #[Test]
    public function hydrate_into_respects_map_from() : void
    {
        $dto = new CDONoConstructorDto();

        $flow = new CreateDataObject();
        $flow->hydrateInto($dto, ['full_name' => 'mapped', 'count' => 1]);

        $this->assertSame('mapped', $dto->name);
    }

    #[Test]
    public function create_throws_for_nonexistent_class() : void
    {
        $this->expectException(ReflectionException::class);

        (new CreateDataObject())->create('NonExistentClass', ['name' => 'test']);
    }

    #[Test]
    public function creates_object_with_all_nullable_fields_as_null() : void
    {
        $dto = (new CreateDataObject())->create(NullableDto::class, ['name' => 'test']);

        $this->assertSame('test', $dto->name);
        $this->assertNull($dto->tag);
    }

    #[Test]
    public function boolean_type_accepts_true_and_false() : void
    {
        $dtoTrue  = (new CreateDataObject())->create(CDOBooleanDto::class, ['active' => true]);
        $dtoFalse = (new CreateDataObject())->create(CDOBooleanDto::class, ['active' => false]);

        $this->assertTrue($dtoTrue->active);
        $this->assertFalse($dtoFalse->active);
    }

    #[Test]
    public function bool_rejects_non_bool_value() : void
    {
        try {
            (new CreateDataObject())->create(CDOBooleanDto::class, ['active' => 'yes']);
            $this->fail('Expected DataTransferFailure');
        } catch (DataTransferFailure $e) {
            $this->assertNotNull($e->violations);
            $found = false;
            foreach ($e->violations as $v) {
                if (str_contains($v->message, 'must be of type bool')) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found);
        }
    }

    #[Test]
    public function array_type_accepts_array() : void
    {
        $dto = (new CreateDataObject())->create(ArrayFieldDto::class, ['tags' => ['a', 'b', 'c']]);

        $this->assertSame(['a', 'b', 'c'], $dto->tags);
    }

    #[Test]
    public function mixed_type_accepts_any_value() : void
    {
        $dto = (new CreateDataObject())->create(MixedDto::class, ['payload' => ['anything' => true]]);

        $this->assertSame(['anything' => true], $dto->payload);
    }

    #[Test]
    public function class_type_rejects_wrong_instance() : void
    {
        try {
            (new CreateDataObject())->create(ClassTypeDto::class, [
                'child' => new stdClass(),
            ]);
            $this->fail('Expected DataTransferFailure');
        } catch (DataTransferFailure $e) {
            $this->assertNotNull($e->violations);
            $found = false;
            foreach ($e->violations as $v) {
                if (str_contains($v->message, 'must be of type')) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Expected class type violation');
        }
    }

    #[Test]
    public function extends_data_object_and_creates_successfully() : void
    {
        $dto = (new CreateDataObject())->create(DataObjectChild::class, ['name' => 'child', 'count' => 5]);

        $this->assertInstanceOf(DataObject::class, $dto);
        $this->assertSame('child', $dto->name);
        $this->assertSame(5, $dto->count);
    }

    #[Test]
    public function empty_list_creates_empty_array() : void
    {
        $dto = (new CreateDataObject())->create(CDOListDto::class, ['items' => []]);

        $this->assertSame([], $dto->items);
    }
}

// -- Test DTOs --

final class ConstructorPromotedDto
{
    public function __construct(
        #[Required]
        #[StringType]
        public string $name,
        #[Optional]
        #[IntegerType]
        public int|null $version = null,
    ) {}
}

final class CDONoConstructorDto extends DataObject
{
    #[Required]
    #[StringType]
    #[MapFrom('full_name')]
    public string $name;

    #[Required]
    #[IntegerType]
    public int $count;
}

final class CreateDataObjectTypedDto
{
    public function __construct(
        #[Required]
        public string $name,
        #[Required]
        public int    $count,
    ) {}
}

final class OptionalDto
{
    public function __construct(
        #[Required]
        public string  $name,
        #[Optional]
        public string|null $tag = null,
    ) {}
}

final class CDODefaultDto
{
    public function __construct(
        #[Required]
        public string $name,
        #[DefaultValue('fallback')]
        public string $role = 'fallback',
    ) {}
}

final class FloatAcceptDto
{
    public function __construct(
        #[Required]
        public float $amount,
    ) {}
}

final class CDOEmailDto
{
    public function __construct(
        #[Required]
        #[Email]
        public string $email,
    ) {}
}

enum TestStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
}

final class CDOEnumDto
{
    public function __construct(
        #[Required]
        public TestStatus $status,
    ) {}
}

final class CDONestedDto
{
    public function __construct(
        #[Required]
        public ConstructorPromotedDto $child,
    ) {}
}

final class CDOListDto
{
    /**
     * @param list<ConstructorPromotedDto> $items
     */
    public function __construct(
        #[Required]
        #[ListOf(ConstructorPromotedDto::class)]
        public array $items,
    ) {}
}

final class LowerCaster
{
    public function cast(mixed $value, string $field) : mixed
    {
        return strtolower((string) $value);
    }
}

final class CDOCastDto
{
    public function __construct(
        #[Required]
        #[CastWith(LowerCaster::class)]
        public string $value,
    ) {}
}

final class CDOMappedDto
{
    public function __construct(
        #[Required]
        #[MapFrom('full_name')]
        public string $name,
    ) {}
}

final class MultiRequiredDto
{
    public function __construct(
        #[Required]
        public string $first,
        #[Required]
        public string $second,
        #[Required]
        public int    $third,
    ) {}
}

final class NullableDto
{
    public function __construct(
        #[Required]
        public string  $name,
        public string|null $tag = null,
    ) {}
}

final class CDOBooleanDto
{
    public function __construct(
        #[Required]
        public bool $active,
    ) {}
}

final class ArrayFieldDto
{
    /**
     * @param list<string> $tags
     */
    public function __construct(
        #[Required]
        public array $tags,
    ) {}
}

final class MixedDto
{
    public function __construct(
        public mixed $payload,
    ) {}
}

final class ClassTypeDto
{
    public function __construct(
        #[Required]
        public ConstructorPromotedDto $child,
    ) {}
}

final class DataObjectChild extends DataObject
{
    #[Required]
    public string $name;

    #[Required]
    public int $count;
}

final class StaticPropertyDto
{
    public static string $staticName = '';

    #[Required]
    public string $name;
}
