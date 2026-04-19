<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\tests\Unit;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\Inputs;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Mapping\MapRequestedInputsToDto;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\RequestedInputs;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization\InputSanitizer;
use InvalidArgumentException;
use NotADtoClass;
use PHPUnit\Framework\TestCase;
use TestRoleEnum;

class RequestedInputsTest extends TestCase
{
    private InputSanitizer          $sanitizer;
    private MapRequestedInputsToDto $mapper;

    public function test_get_prefers_body_over_query_for_same_key()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: ['foo' => 'query'],
                parsedBody: ['foo' => 'body'],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );

        $this->assertEquals(expected: 'body', actual: $inputs->get(key: 'foo'));
    }

    public function test_all_prefers_body_over_query_for_same_key()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: ['foo' => 'query', 'bar' => 'baz'],
                parsedBody: ['foo' => 'body'],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );

        $all = $inputs->all();
        $this->assertEquals(expected: 'body', actual: $all['foo']);
        $this->assertEquals(expected: 'baz', actual: $all['bar']);
    }

    public function test_bool_accepts_valid_boolean_strings()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: [
                    't' => 'true',
                    'f' => 'false',
                    '1' => '1',
                    '0' => '0',
                    'on' => 'on',
                    'off' => 'off',
                    'yes' => 'yes',
                    'no' => 'no',
                ],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );

        $this->assertTrue(condition: $inputs->bool(key: 't'));
        $this->assertFalse(condition: $inputs->bool(key: 'f'));
        $this->assertTrue(condition: $inputs->bool(key: '1'));
        $this->assertFalse(condition: $inputs->bool(key: '0'));
        $this->assertTrue(condition: $inputs->bool(key: 'on'));
        $this->assertFalse(condition: $inputs->bool(key: 'off'));
        $this->assertTrue(condition: $inputs->bool(key: 'yes', default: true));
        $this->assertFalse(condition: $inputs->bool(key: 'no'));
    }

    public function test_bool_returns_default_for_invalid_value()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: ['foo' => 'not-a-bool'],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );
        $this->assertTrue(condition: $inputs->bool(key: 'foo', default: true));
        $this->assertFalse(condition: $inputs->bool(key: 'foo', default: false));
    }

    public function test_int_accepts_valid_integer_string()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: ['foo' => '123'],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );
        $this->assertEquals(expected: 123, actual: $inputs->int(key: 'foo'));
    }

    public function test_int_returns_default_for_invalid_string()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: ['foo' => 'abc'],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );
        $this->assertEquals(expected: 5, actual: $inputs->int(key: 'foo', default: 5));
    }

    public function test_float_accepts_valid_float_string()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: ['foo' => '1.23'],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );
        $this->assertEquals(expected: 1.23, actual: $inputs->float(key: 'foo'));
    }

    public function test_float_returns_default_for_invalid_value()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: ['foo' => 'abc'],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );
        $this->assertEquals(expected: 1.1, actual: $inputs->float(key: 'foo', default: 1.1));
    }

    public function test_string_casts_scalar_to_string()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: ['foo' => 123],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );
        $this->assertEquals(expected: '123', actual: $inputs->string(key: 'foo'));
    }

    public function test_array_returns_array_as_is()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: ['foo' => ['a', 'b']],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );
        $this->assertEquals(expected: ['a', 'b'], actual: $inputs->array(key: 'foo'));
    }

    public function test_array_returns_default_for_scalar()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: ['foo' => 'bar'],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );
        $this->assertEquals(expected: [], actual: $inputs->array(key: 'foo'));
    }

    public function test_has_uses_array_key_exists_not_isset()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: ['foo' => null],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );
        $this->assertTrue(condition: $inputs->has(key: 'foo'));
        $this->assertFalse(condition: $inputs->hasNonNull(key: 'foo'));
    }

    public function test_sanitized_html_prevents_xss()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: ['content' => '<script>alert("xss")</script>'],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );
        $safe   = $inputs->sanitizedHtml(key: 'content');

        $this->assertStringNotContainsString(needle: '<script>', haystack: $safe);
        $this->assertStringContainsString(needle: '&lt;script&gt;', haystack: $safe);
    }

    public function test_value_returns_inputvalue_with_source()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: ['from' => 'query'],
                parsedBody: ['from' => 'body'],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );

        $value = $inputs->value(key: 'from');
        $this->assertTrue(condition: $value->isFromBody());
        $this->assertFalse(condition: $value->isFromQuery());
        $this->assertEquals(expected: 'body', actual: $value->value);
    }

    public function test_query_and_body_accessors()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: ['q' => 'search'],
                parsedBody: ['b' => 'data'],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );

        $this->assertEquals(expected: 'search', actual: $inputs->fromQuery(key: 'q'));
        $this->assertEquals(expected: 'data', actual: $inputs->fromBody(key: 'b'));
    }

    public function test_only_returns_only_specified_keys()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: [
                    'foo' => 'bar',
                    'baz' => 'qux',
                    'extra' => 'value',
                ],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );

        $result = $inputs->only('foo', 'baz');

        $this->assertEquals(expected: ['foo' => 'bar', 'baz' => 'qux'], actual: $result);
    }

    public function test_only_with_body_preference()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: ['shared' => 'query'],
                parsedBody: ['shared' => 'body'],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );

        $result = $inputs->only('shared');

        $this->assertEquals(expected: ['shared' => 'body'], actual: $result);
    }

    public function test_except_excludes_specified_keys()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: [
                    'foo' => 'bar',
                    'baz' => 'qux',
                    'keep' => 'this',
                ],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );

        $result = $inputs->except('foo', 'baz');

        $this->assertEquals(expected: ['keep' => 'this'], actual: $result);
    }

    public function test_bool_true_values_blacklist()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: [
                    'true' => 'true',
                    'one' => '1',
                    'on' => 'on',
                ],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );

        $this->assertTrue(condition: $inputs->bool(key: 'true'));
        $this->assertTrue(condition: $inputs->bool(key: 'one'));
        $this->assertTrue(condition: $inputs->bool(key: 'on'));
    }

    public function test_bool_false_values_blacklist()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: [
                    'false' => 'false',
                    'zero' => '0',
                    'off' => 'off',
                ],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );

        $this->assertFalse(condition: $inputs->bool(key: 'false'));
        $this->assertFalse(condition: $inputs->bool(key: 'zero'));
        $this->assertFalse(condition: $inputs->bool(key: 'off'));
    }

    public function test_bool_invalid_returns_default()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: [
                    'yes' => 'yes',
                    'no' => 'no',
                    'garbage' => 'garbage',
                    'array' => [1, 2, 3],
                    'null' => null,
                ],
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );

        $this->assertTrue(condition: $inputs->bool(key: 'yes', default: true));
        $this->assertFalse(condition: $inputs->bool(key: 'no', default: false));
        $this->assertTrue(condition: $inputs->bool(key: 'garbage', default: true));
        $this->assertTrue(condition: $inputs->bool(key: 'array', default: true));
        $this->assertTrue(condition: $inputs->bool(key: 'null', default: true));
    }

    public function test_has_vs_has_non_null_semantics()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: ['explicit_null' => null, 'value' => 1]
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );

        // Strict presence
        $this->assertTrue(condition: $inputs->has(key: 'explicit_null'));
        $this->assertFalse(condition: $inputs->has(key: 'missing'));

        // Non-null presence
        $this->assertFalse(condition: $inputs->hasNonNull(key: 'explicit_null'));
        $this->assertTrue(condition: $inputs->hasNonNull(key: 'value'));
    }

    public function test_enum_hydration()
    {
        if (!enum_exists('TestRoleEnum')) {
            eval('enum TestRoleEnum: string { case Admin = "admin"; case User = "user"; }');
        }

        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: ['role' => 'admin', 'invalid' => 'superadmin']
            ),
            sanitizer: $this->sanitizer,
            mapper: $this->mapper,
        );

        $this->assertEquals(expected: TestRoleEnum::Admin, actual: $inputs->enum(key: 'role', enumClass: TestRoleEnum::class));
        $this->assertNull(actual: $inputs->enum(key: 'invalid', enumClass: TestRoleEnum::class));
        $this->assertEquals(expected: TestRoleEnum::User, actual: $inputs->enum(key: 'invalid', enumClass: TestRoleEnum::class, default: TestRoleEnum::User));
    }

    public function test_dto_mapping_throws_when_class_missing()
    {
        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody([], []),
            sanitizer: $this->sanitizer,
            mapper: clone $this->mapper,
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DTO class does not exist');

        $inputs->as(dtoClass: 'NonExistentDtoClass');
    }

    public function test_dto_mapping_throws_when_not_abstract_dto()
    {
        if (!class_exists('NotADtoClass')) {
            eval('class NotADtoClass {}');
        }

        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody([], []),
            sanitizer: $this->sanitizer,
            mapper: clone $this->mapper,
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must extend');

        $inputs->as(dtoClass: NotADtoClass::class);
    }

    public function test_dto_mapping_path()
    {
        if (!class_exists('Avax\HTTP\Request\Tests\Unit\TestDtoClass')) {
            eval('
                namespace Avax\HTTP\Request\tests\Unit;
                use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;

                class TestDtoClass extends AbstractDTO {
                    public string $name;
                    public int $age;
                    public function __construct(array $payload) {
                        $this->name = $payload["name"] ?? "";
                        $this->age = (int)($payload["age"] ?? 0);
                    }
                }
            ');
        }

        if (!class_exists('NotADtoClass')) {
            class NotADtoClass {}
        }

        if (!enum_exists('TestRoleEnum')) {
            enum TestRoleEnum: string {
                case Admin = 'admin';
                case User = 'user';
            }
        }

        $inputs = new RequestedInputs(
            inputs: Inputs::fromQueryAndBody(
                queryParams: [],
                parsedBody: ['name' => 'John', 'age' => '30']
            ),
            sanitizer: $this->sanitizer,
            mapper: clone $this->mapper,
        );

        $dto = $inputs->as(dtoClass: 'Avax\HTTP\Request\tests\Unit\TestDtoClass');
        
        $this->assertInstanceOf(expected: 'Avax\HTTP\Request\tests\Unit\TestDtoClass', actual: $dto);
        $this->assertEquals(expected: 'John', actual: $dto->name);
        $this->assertEquals(expected: 30, actual: $dto->age);
    }

    protected function setUp() : void
    {
        $this->sanitizer = new InputSanitizer;
        $this->mapper    = new MapRequestedInputsToDto;
    }
}
