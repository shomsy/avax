<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\DataTransfer;

use Attribute;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\AlphaNum;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\AlphaNumOrEmail;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Between;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CastWith;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\DefaultValue;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Email;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Hidden;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\ListOf;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\MapFrom;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Max;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Min;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\RegexPattern;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataTransfer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AttributeReadingTest extends TestCase
{
    // -- Email attribute tests --

    #[Test]
    public function email_validates_correct_email() : void
    {
        $email = new Email();
        $email->validate('user@example.com', 'email');
        $this->assertInstanceOf(Email::class, $email);
    }

    #[Test]
    public function email_rejects_invalid_email() : void
    {
        $email = new Email();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be a valid email address');

        $email->validate('not-an-email', 'email');
    }

    #[Test]
    public function email_rejects_empty_string() : void
    {
        $email = new Email();

        $this->expectException(InvalidArgumentException::class);

        $email->validate('', 'email');
    }

    #[Test]
    public function email_message_includes_field_name() : void
    {
        $email = new Email();

        try {
            $email->validate('bad', 'user_email');
            $this->fail('Expected exception');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('user_email', $e->getMessage());
        }
    }

    // -- Min attribute tests --

    #[Test]
    public function min_validates_string_length() : void
    {
        $min = new Min(3);
        $min->validate('hello', 'name');
        $this->assertInstanceOf(Min::class, $min);
    }

    #[Test]
    public function min_rejects_short_string() : void
    {
        $min = new Min(5);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('at least 5');

        $min->validate('ab', 'name');
    }

    #[Test]
    public function min_validates_numeric_value() : void
    {
        $min = new Min(10);
        $min->validate(15, 'age');
        $this->assertInstanceOf(Min::class, $min);
    }

    #[Test]
    public function min_rejects_small_number() : void
    {
        $min = new Min(10);

        $this->expectException(InvalidArgumentException::class);

        $min->validate(5, 'age');
    }

    #[Test]
    public function min_validates_array_count() : void
    {
        $min = new Min(2);
        $min->validate(['a', 'b', 'c'], 'items');
        $this->assertInstanceOf(Min::class, $min);
    }

    #[Test]
    public function min_rejects_array_with_few_items() : void
    {
        $min = new Min(3);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('at least 3 items');

        $min->validate(['a'], 'items');
    }

    #[Test]
    public function min_exact_boundary_passes() : void
    {
        $min = new Min(3);
        $min->validate('abc', 'name');
        $this->assertInstanceOf(Min::class, $min);
    }

    // -- Max attribute tests --

    #[Test]
    public function max_validates_string_length() : void
    {
        $max = new Max(10);
        $max->validate('short', 'name');
        $this->assertInstanceOf(Max::class, $max);
    }

    #[Test]
    public function max_rejects_long_string() : void
    {
        $max = new Max(5);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not exceed 5');

        $max->validate('too long string', 'name');
    }

    #[Test]
    public function max_validates_numeric_value() : void
    {
        $max = new Max(100);
        $max->validate(50, 'score');
        $this->assertInstanceOf(Max::class, $max);
    }

    #[Test]
    public function max_rejects_large_number() : void
    {
        $max = new Max(100);

        $this->expectException(InvalidArgumentException::class);

        $max->validate(200, 'score');
    }

    #[Test]
    public function max_validates_array_count() : void
    {
        $max = new Max(3);
        $max->validate(['a', 'b'], 'items');
        $this->assertInstanceOf(Max::class, $max);
    }

    #[Test]
    public function max_rejects_array_with_too_many_items() : void
    {
        $max = new Max(2);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not have more than 2');

        $max->validate(['a', 'b', 'c'], 'items');
    }

    #[Test]
    public function max_exact_boundary_passes() : void
    {
        $max = new Max(3);
        $max->validate('abc', 'name');
        $this->assertInstanceOf(Max::class, $max);
    }

    // -- Between attribute tests --

    #[Test]
    public function between_validates_string_length_in_range() : void
    {
        $between = new Between(3, 10);
        $between->validate('hello', 'name');
        $this->assertInstanceOf(Between::class, $between);
    }

    #[Test]
    public function between_rejects_too_short_string() : void
    {
        $between = new Between(3, 10);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('between 3 and 10');

        $between->validate('ab', 'name');
    }

    #[Test]
    public function between_rejects_too_long_string() : void
    {
        $between = new Between(3, 10);

        $this->expectException(InvalidArgumentException::class);

        $between->validate('this is way too long', 'name');
    }

    #[Test]
    public function between_validates_numeric_in_range() : void
    {
        $between = new Between(10, 20);
        $between->validate(15, 'score');
        $this->assertInstanceOf(Between::class, $between);
    }

    #[Test]
    public function between_rejects_number_below_range() : void
    {
        $between = new Between(10, 20);

        $this->expectException(InvalidArgumentException::class);

        $between->validate(5, 'score');
    }

    #[Test]
    public function between_rejects_number_above_range() : void
    {
        $between = new Between(10, 20);

        $this->expectException(InvalidArgumentException::class);

        $between->validate(25, 'score');
    }

    #[Test]
    public function between_validates_array_count_in_range() : void
    {
        $between = new Between(2, 4);
        $between->validate(['a', 'b', 'c'], 'items');
        $this->assertInstanceOf(Between::class, $between);
    }

    #[Test]
    public function between_rejects_array_outside_range() : void
    {
        $between = new Between(2, 4);

        $this->expectException(InvalidArgumentException::class);

        $between->validate(['a'], 'items');
    }

    #[Test]
    public function between_exact_boundaries_pass() : void
    {
        $between = new Between(3, 5);
        $between->validate('abc', 'name');
        $between->validate('abcde', 'name');
        $this->assertInstanceOf(Between::class, $between);
    }

    // -- RegexPattern attribute tests --

    #[Test]
    public function regex_validates_matching_pattern() : void
    {
        $regex = new RegexPattern('/^[a-z]+$/');
        $regex->validate('hello', 'name');
        $this->assertInstanceOf(RegexPattern::class, $regex);
    }

    #[Test]
    public function regex_rejects_non_matching_pattern() : void
    {
        $regex = new RegexPattern('/^[a-z]+$/');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('required pattern');

        $regex->validate('Hello123', 'name');
    }

    #[Test]
    public function regex_validates_phone_pattern() : void
    {
        $regex = new RegexPattern('/^\+?[0-9]{10,15}$/');
        $regex->validate('+1234567890', 'phone');
        $this->assertInstanceOf(RegexPattern::class, $regex);
    }

    #[Test]
    public function regex_rejects_invalid_phone() : void
    {
        $regex = new RegexPattern('/^\+?[0-9]{10,15}$/');

        $this->expectException(InvalidArgumentException::class);

        $regex->validate('abc-def-ghij', 'phone');
    }

    // -- AlphaNum attribute tests --

    #[Test]
    public function alphanum_validates_alphanumeric_string() : void
    {
        $alphanum = new AlphaNum();
        $alphanum->validate('abc123', 'code');
        $this->assertInstanceOf(AlphaNum::class, $alphanum);
    }

    #[Test]
    public function alphanum_rejects_special_characters() : void
    {
        $alphanum = new AlphaNum();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('alphanumeric');

        $alphanum->validate('abc-123', 'code');
    }

    #[Test]
    public function alphanum_rejects_spaces() : void
    {
        $alphanum = new AlphaNum();

        $this->expectException(InvalidArgumentException::class);

        $alphanum->validate('abc 123', 'code');
    }

    #[Test]
    public function alphanum_message_includes_field_name() : void
    {
        $alphanum = new AlphaNum();

        try {
            $alphanum->validate('bad-value!', 'product_code');
            $this->fail('Expected exception');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('product_code', $e->getMessage());
        }
    }

    // -- AlphaNumOrEmail attribute tests --

    #[Test]
    public function alphanum_or_email_accepts_valid_email() : void
    {
        $attr = new AlphaNumOrEmail();
        $attr->validate('user@example.com', 'identifier');
        $this->assertInstanceOf(AlphaNumOrEmail::class, $attr);
    }

    #[Test]
    public function alphanum_or_email_accepts_alphanumeric() : void
    {
        $attr = new AlphaNumOrEmail();
        $attr->validate('user123', 'identifier');
        $this->assertInstanceOf(AlphaNumOrEmail::class, $attr);
    }

    #[Test]
    public function alphanum_or_email_rejects_invalid_value() : void
    {
        $attr = new AlphaNumOrEmail();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('alphanumeric or a valid email');

        $attr->validate('bad value!', 'identifier');
    }

    #[Test]
    public function alphanum_or_email_rejects_special_characters() : void
    {
        $attr = new AlphaNumOrEmail();

        $this->expectException(InvalidArgumentException::class);

        $attr->validate('user@name!', 'identifier');
    }

    // -- Integration: attributes work with DataTransfer --

    #[Test]
    public function email_attribute_validates_through_data_transfer() : void
    {
        $result = DataTransfer::tryCreate(EmailTestDto::class, ['email' => 'not-email']);

        $this->assertTrue($result->isFailure());
        $this->assertTrue($result->hasViolations());
        $this->assertSame('email', $result->violations()->getIterator()->current()->field);
    }

    #[Test]
    public function min_attribute_validates_through_data_transfer() : void
    {
        $result = DataTransfer::tryCreate(MinTestDto::class, ['name' => 'ab']);

        $this->assertTrue($result->isFailure());
        $this->assertTrue($result->hasViolations());
    }

    #[Test]
    public function max_attribute_validates_through_data_transfer() : void
    {
        $result = DataTransfer::tryCreate(MaxTestDto::class, ['name' => 'toolongvalue']);

        $this->assertTrue($result->isFailure());
        $this->assertTrue($result->hasViolations());
    }

    #[Test]
    public function between_attribute_validates_through_data_transfer() : void
    {
        $result = DataTransfer::tryCreate(BetweenTestDto::class, ['name' => 'ab']);

        $this->assertTrue($result->isFailure());
    }

    #[Test]
    public function regex_attribute_validates_through_data_transfer() : void
    {
        $result = DataTransfer::tryCreate(RegexTestDto::class, ['code' => 'INVALID123']);

        $this->assertTrue($result->isFailure());
    }

    #[Test]
    public function alphanum_attribute_validates_through_data_transfer() : void
    {
        $result = DataTransfer::tryCreate(AlphaNumTestDto::class, ['code' => 'bad-code']);

        $this->assertTrue($result->isFailure());
    }

    #[Test]
    public function multiple_attributes_all_validate_through_data_transfer() : void
    {
        // Min + Max + AlphaNum
        $result = DataTransfer::tryCreate(MultiAttrDto::class, ['value' => 'ab!']);

        $this->assertTrue($result->isFailure());
        $this->assertGreaterThanOrEqual(1, $result->violations()->count());
    }

    #[Test]
    public function all_attributes_pass_through_data_transfer() : void
    {
        $result = DataTransfer::tryCreate(MultiAttrDto::class, ['value' => 'hello']);

        $this->assertTrue($result->isSuccess());
        $this->assertSame('hello', $result->object()->value);
    }

    // -- Attribute structure tests --

    #[Test]
    public function map_from_has_name_property() : void
    {
        $mapFrom = new MapFrom('full_name');

        $this->assertSame('full_name', $mapFrom->name);
    }

    #[Test]
    public function default_value_has_value_property() : void
    {
        $default = new DefaultValue('fallback');

        $this->assertSame('fallback', $default->value);
    }

    #[Test]
    public function default_value_can_hold_any_mixed_type() : void
    {
        $this->assertSame(42, (new DefaultValue(42))->value);
        $this->assertTrue((new DefaultValue(true))->value);
        $this->assertSame(['a'], (new DefaultValue(['a']))->value);
    }

    #[Test]
    public function list_of_has_class_property() : void
    {
        $listOf = new ListOf(self::class);

        $this->assertSame(self::class, $listOf->class);
    }

    #[Test]
    public function list_of_of_method_returns_class() : void
    {
        $listOf = new ListOf(self::class);

        $this->assertSame(self::class, $listOf->of());
    }

    #[Test]
    public function cast_with_has_caster_class_property() : void
    {
        $castWith = new CastWith(TestAttributeCaster::class);

        $this->assertSame(TestAttributeCaster::class, $castWith->casterClass);
    }

    #[Test]
    public function hidden_has_no_properties() : void
    {
        $hidden     = new Hidden();
        $reflection = new ReflectionClass($hidden);

        $this->assertEmpty($reflection->getProperties());
    }

    // -- Attribute target tests --

    #[Test]
    public function email_attribute_targets_property_and_parameter() : void
    {
        $reflection = new ReflectionClass(Email::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertNotEmpty($attributes);
    }

    #[Test]
    public function required_attribute_targets_property_and_parameter() : void
    {
        $reflection = new ReflectionClass(Required::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertNotEmpty($attributes);
    }

    // -- Float and integer type attribute tests via DataTransfer --

    #[Test]
    public function min_with_float_value() : void
    {
        $min = new Min(5.5);
        $min->validate(6.0, 'score');
        $this->assertInstanceOf(Min::class, $min);
    }

    #[Test]
    public function max_with_float_value() : void
    {
        $max = new Max(10.5);
        $max->validate(10.0, 'score');
        $this->assertInstanceOf(Max::class, $max);
    }

    #[Test]
    public function between_with_float_values() : void
    {
        $between = new Between(5.0, 10.0);
        $between->validate(7.5, 'score');
        $this->assertInstanceOf(Between::class, $between);
    }
}

// -- Test DTOs for integration --

final class EmailTestDto
{
    public function __construct(
        #[Email]
        public string $email,
    ) {}
}

final class MinTestDto
{
    public function __construct(
        #[Min(3)]
        public string $name,
    ) {}
}

final class MaxTestDto
{
    public function __construct(
        #[Max(5)]
        public string $name,
    ) {}
}

final class BetweenTestDto
{
    public function __construct(
        #[Between(3, 10)]
        public string $name,
    ) {}
}

final class RegexTestDto
{
    public function __construct(
        #[RegexPattern('/^[a-z]+$/')]
        public string $code,
    ) {}
}

final class AlphaNumTestDto
{
    public function __construct(
        #[AlphaNum]
        public string $code,
    ) {}
}

final class MultiAttrDto
{
    public function __construct(
        #[Min(3)]
        #[Max(10)]
        #[AlphaNum]
        public string $value,
    ) {}
}

final class TestAttributeCaster
{
    public function cast(mixed $value, string $field) : mixed
    {
        return $value;
    }
}
