<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\DataTransfer;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\AlphaNum;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\AlphaNumOrEmail;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Between;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CastWith;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\DefaultValue;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Email;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Hidden;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\IntegerType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\ListOf;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\MapFrom;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Max;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Min;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Optional;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\RegexPattern;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\StringType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferFailure;
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataTransfer;
use PHPUnit\Framework\TestCase;
use stdClass;

final class DataTransferCapabilitiesTest extends TestCase
{
    public function test_create_typed_object_from_array() : void
    {
        $dto = DataTransfer::create(SimpleDto::class, ['name' => 'AvaX', 'version' => 1]);

        $this->assertInstanceOf(SimpleDto::class, $dto);
        $this->assertSame('AvaX', $dto->name);
        $this->assertSame(1, $dto->version);
    }

    public function test_validates_required_field() : void
    {
        $this->expectException(DataTransferFailure::class);
        $this->expectExceptionMessage('Data validation failed.');

        DataTransfer::create(SimpleDto::class, ['version' => 1]);
    }

    public function test_validates_string_type() : void
    {
        try {
            DataTransfer::create(TypedDto::class, ['name' => 42, 'count' => 99]);
            $this->fail('Expected DataTransferFailure');
        } catch (DataTransferFailure $e) {
            $this->assertNotNull($e->violations);
            $this->assertGreaterThanOrEqual(1, $e->violations->count());
        }
    }

    public function test_validates_integer_type() : void
    {
        try {
            DataTransfer::create(TypedDto::class, ['name' => 'test', 'count' => 'not-int']);
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
            $this->assertTrue($found, 'Expected type violation for count field');
        }
    }

    public function test_validates_min_constraint() : void
    {
        try {
            DataTransfer::create(ConstrainedDto::class, ['username' => 'ab']);
            $this->fail('Expected DataTransferFailure');
        } catch (DataTransferFailure $e) {
            $this->assertNotNull($e->violations);
            $found = false;
            foreach ($e->violations as $v) {
                if (str_contains($v->message, 'at least 3')) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Expected min violation');
        }
    }

    public function test_validates_max_constraint() : void
    {
        try {
            DataTransfer::create(ConstrainedDto::class, ['username' => 'toolong']);
            $this->fail('Expected DataTransferFailure');
        } catch (DataTransferFailure $e) {
            $this->assertNotNull($e->violations);
            $found = false;
            foreach ($e->violations as $v) {
                if (str_contains($v->message, 'not exceed 5')) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Expected max violation');
        }
    }

    public function test_validates_email_constraint() : void
    {
        try {
            DataTransfer::create(EmailDto::class, ['email' => 'not-an-email']);
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
            $this->assertTrue($found, 'Expected email violation');
        }
    }

    public function test_validates_regex_pattern() : void
    {
        try {
            DataTransfer::create(PasswordDto::class, ['password' => 'weak']);
            $this->fail('Expected DataTransferFailure');
        } catch (DataTransferFailure $e) {
            $this->assertNotNull($e->violations);
            $found = false;
            foreach ($e->violations as $v) {
                if (str_contains($v->message, 'required pattern')) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Expected regex violation');
        }
    }

    public function test_collects_multiple_violations() : void
    {
        try {
            DataTransfer::create(ConstrainedDto::class, []);
            $this->fail('Expected DataTransferFailure');
        } catch (DataTransferFailure $e) {
            $this->assertNotNull($e->violations);
            $this->assertGreaterThanOrEqual(1, $e->violations->count());
        }
    }

    public function test_supports_default_value() : void
    {
        $dto = DataTransfer::create(DefaultDto::class, []);

        $this->assertSame('default', $dto->name);
    }

    public function test_supports_map_from() : void
    {
        $dto = DataTransfer::create(MappedDto::class, ['full_name' => 'AvaX']);

        $this->assertSame('AvaX', $dto->name);
    }

    public function test_supports_list_of_nested_dto_casting() : void
    {
        $dto = DataTransfer::create(ListDto::class, [
            'items' => [
                ['name' => 'first', 'version' => 1],
                ['name' => 'second', 'version' => 2],
            ],
        ]);

        $this->assertCount(2, $dto->items);
        $this->assertInstanceOf(SimpleDto::class, $dto->items[0]);
        $this->assertInstanceOf(SimpleDto::class, $dto->items[1]);
        $this->assertSame('first', $dto->items[0]->name);
        $this->assertSame('second', $dto->items[1]->name);
    }

    public function test_supports_nested_object_casting() : void
    {
        $dto = DataTransfer::create(NestedDto::class, [
            'child' => ['name' => 'child', 'version' => 42],
        ]);

        $this->assertInstanceOf(SimpleDto::class, $dto->child);
        $this->assertSame('child', $dto->child->name);
        $this->assertSame(42, $dto->child->version);
    }

    public function test_supports_backed_enum_casting() : void
    {
        $dto = DataTransfer::create(EnumDto::class, ['status' => 'active']);

        $this->assertSame(Status::Active, $dto->status);
    }

    public function test_supports_custom_caster() : void
    {
        $dto = DataTransfer::create(CastDto::class, ['value' => 'HELLO']);

        $this->assertSame('hello', $dto->value);
    }

    public function test_serializes_to_array() : void
    {
        $dto   = DataTransfer::create(SimpleDto::class, ['name' => 'AvaX', 'version' => 1]);
        $array = DataTransfer::toArray($dto);

        $this->assertSame(['name' => 'AvaX', 'version' => 1], $array);
    }

    public function test_serializes_to_json() : void
    {
        $dto  = DataTransfer::create(SimpleDto::class, ['name' => 'AvaX', 'version' => 1]);
        $json = DataTransfer::toJson($dto);

        $this->assertJsonStringEqualsJsonString('{"name":"AvaX","version":1}', $json);
    }

    public function test_serializes_to_stdclass() : void
    {
        $dto = DataTransfer::create(SimpleDto::class, ['name' => 'AvaX', 'version' => 1]);
        $std = DataTransfer::toStdClass($dto);

        $this->assertInstanceOf(stdClass::class, $std);
        $this->assertSame('AvaX', $std->name);
        $this->assertSame(1, $std->version);
    }

    public function test_try_create_returns_success() : void
    {
        $result = DataTransfer::tryCreate(SimpleDto::class, ['name' => 'AvaX', 'version' => 1]);

        $this->assertTrue($result->isSuccess());
        $this->assertInstanceOf(SimpleDto::class, $result->object());
    }

    public function test_try_create_returns_failure() : void
    {
        $result = DataTransfer::tryCreate(SimpleDto::class, []);

        $this->assertTrue($result->isFailure());
    }

    public function test_validates_alphanum() : void
    {
        try {
            DataTransfer::create(AlphaNumDto::class, ['code' => 'abc-123']);
            $this->fail('Expected DataTransferFailure');
        } catch (DataTransferFailure $e) {
            $this->assertNotNull($e->violations);
            $found = false;
            foreach ($e->violations as $v) {
                if (str_contains($v->message, 'alphanumeric')) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Expected alphanum violation');
        }
    }

    public function test_validates_alphanum_or_email() : void
    {
        // Valid email passes
        $dto = DataTransfer::create(AlphaNumEmailDto::class, ['identifier' => 'user@example.com']);
        $this->assertSame('user@example.com', $dto->identifier);

        // Valid alphanum passes
        $dto = DataTransfer::create(AlphaNumEmailDto::class, ['identifier' => 'user123']);
        $this->assertSame('user123', $dto->identifier);

        // Invalid fails
        $this->expectException(DataTransferFailure::class);
        DataTransfer::create(AlphaNumEmailDto::class, ['identifier' => 'user@ name']);
    }

    public function test_hidden_field_excluded_from_array() : void
    {
        $dto   = DataTransfer::create(HiddenDto::class, ['name' => 'public', 'secret' => 'hidden']);
        $array = DataTransfer::toArray($dto);

        $this->assertArrayNotHasKey('secret', $array);
        $this->assertArrayHasKey('name', $array);
    }

    public function test_validates_alpha_num_or_email_constraint() : void
    {
        $this->expectException(DataTransferFailure::class);

        DataTransfer::create(AlphaNumEmailDto::class, ['identifier' => 'bad value!']);
    }

    public function test_validates_between_constraint() : void
    {
        try {
            DataTransfer::create(BetweenDto::class, ['length' => 'ab']);
            $this->fail('Expected DataTransferFailure');
        } catch (DataTransferFailure $e) {
            $this->assertNotNull($e->violations);
            $found = false;
            foreach ($e->violations as $v) {
                if (str_contains($v->message, 'between 3 and 10')) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Expected between violation');
        }

        // Valid length passes
        $dto = DataTransfer::create(BetweenDto::class, ['length' => 'hello']);
        $this->assertSame('hello', $dto->length);
    }
}

// -- Test DTOs --

final class SimpleDto
{
    public function __construct(
        #[Required]
        #[StringType]
        public string $name,
        #[Optional]
        #[IntegerType]
        public ?int   $version = null,
    ) {}
}

final class TypedDto
{
    public function __construct(
        #[Required]
        #[StringType]
        public string $name,
        #[Required]
        #[IntegerType]
        public int    $count,
    ) {}
}

final class ConstrainedDto
{
    public function __construct(
        #[Required]
        #[StringType]
        #[Min(3)]
        #[Max(5)]
        public string $username,
    ) {}
}

final class EmailDto
{
    public function __construct(
        #[Required]
        #[StringType]
        #[Email]
        public string $email,
    ) {}
}

final class PasswordDto
{
    public function __construct(
        #[Required]
        #[StringType]
        #[Min(8)]
        #[RegexPattern('/^(?=.*[A-Za-z])(?=.*\d).{8,}$/')]
        public string $password,
    ) {}
}

final class DefaultDto
{
    public function __construct(
        #[DefaultValue('default')]
        public string $name = 'default',
    ) {}
}

final class MappedDto
{
    public function __construct(
        #[Required]
        #[MapFrom('full_name')]
        public string $name,
    ) {}
}

final class ListDto
{
    /**
     * @param list<SimpleDto> $items
     */
    public function __construct(
        #[Required]
        #[ListOf(SimpleDto::class)]
        public array $items,
    ) {}
}

final class NestedDto
{
    public function __construct(
        #[Required]
        public SimpleDto $child,
    ) {}
}

enum Status: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
}

final class EnumDto
{
    public function __construct(
        #[Required]
        public Status $status,
    ) {}
}

final class LowercaseCaster
{
    public function cast(mixed $value, string $field) : mixed
    {
        return strtolower((string) $value);
    }
}

final class CastDto
{
    public function __construct(
        #[Required]
        #[CastWith(LowercaseCaster::class)]
        public string $value,
    ) {}
}

final class AlphaNumDto
{
    public function __construct(
        #[Required]
        #[StringType]
        #[AlphaNum]
        public string $code,
    ) {}
}

final class AlphaNumEmailDto
{
    public function __construct(
        #[Required]
        #[AlphaNumOrEmail]
        public string $identifier,
    ) {}
}

final class HiddenDto
{
    public function __construct(
        #[Required]
        public string $name,
        #[Hidden]
        public string $secret,
    ) {}
}

final class BetweenDto
{
    public function __construct(
        #[Required]
        #[StringType]
        #[Between(3, 10)]
        public string $length,
    ) {}
}
