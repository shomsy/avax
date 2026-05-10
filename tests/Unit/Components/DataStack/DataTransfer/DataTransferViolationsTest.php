<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\DataTransfer;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferException;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferFailure;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferResult;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferViolation;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferViolations;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use stdClass;

final class DataTransferViolationsTest extends TestCase
{
    #[Test]
    public function violation_has_field_and_message() : void
    {
        $violation = new DataTransferViolation(
            field  : 'email',
            message: 'Must be a valid email.',
        );

        $this->assertSame('email', $violation->field);
        $this->assertSame('Must be a valid email.', $violation->message);
    }

    #[Test]
    public function violation_can_have_optional_code() : void
    {
        $violation = new DataTransferViolation(
            field  : 'email',
            message: 'Invalid format.',
            code   : 'INVALID_EMAIL',
        );

        $this->assertSame('INVALID_EMAIL', $violation->code);
    }

    #[Test]
    public function violation_can_have_invalid_value() : void
    {
        $violation = new DataTransferViolation(
            field       : 'age',
            message     : 'Must be positive.',
            invalidValue: -5,
        );

        $this->assertSame(-5, $violation->invalidValue);
    }

    #[Test]
    public function violation_json_serializes() : void
    {
        $violation = new DataTransferViolation(
            field       : 'name',
            message     : 'Required.',
            code        : 'REQUIRED',
            invalidValue: null,
        );

        $json    = json_encode($violation);
        $decoded = json_decode($json, true);

        $this->assertSame('name', $decoded['field']);
        $this->assertSame('Required.', $decoded['message']);
        $this->assertSame('REQUIRED', $decoded['code']);
        $this->assertNull($decoded['invalidValue']);
    }

    #[Test]
    public function violations_empty_creates_empty_collection() : void
    {
        $violations = DataTransferViolations::empty();

        $this->assertTrue($violations->isEmpty());
        $this->assertSame(0, $violations->count());
    }

    #[Test]
    public function violations_from_creates_from_array() : void
    {
        $v1 = new DataTransferViolation(field: 'a', message: 'error a');
        $v2 = new DataTransferViolation(field: 'b', message: 'error b');

        $violations = DataTransferViolations::from([$v1, $v2]);

        $this->assertSame(2, $violations->count());
        $this->assertFalse($violations->isEmpty());
    }

    #[Test]
    public function violations_add_returns_new_instance() : void
    {
        $original  = DataTransferViolations::empty();
        $violation = new DataTransferViolation(field: 'name', message: 'Required.');

        $added = $original->add($violation);

        $this->assertTrue($original->isEmpty());
        $this->assertFalse($added->isEmpty());
        $this->assertSame(1, $added->count());
    }

    #[Test]
    public function violations_is_iterable() : void
    {
        $v1 = new DataTransferViolation(field: 'a', message: 'a');
        $v2 = new DataTransferViolation(field: 'b', message: 'b');

        $violations = DataTransferViolations::from([$v1, $v2]);

        $items = iterator_to_array($violations);
        $this->assertCount(2, $items);
        $this->assertSame($v1, $items[0]);
        $this->assertSame($v2, $items[1]);
    }

    #[Test]
    public function violations_json_serializes_all() : void
    {
        $v1 = new DataTransferViolation(field: 'a', message: 'err a');
        $v2 = new DataTransferViolation(field: 'b', message: 'err b');

        $violations = DataTransferViolations::from([$v1, $v2]);

        $json    = json_encode($violations);
        $decoded = json_decode($json, true);

        $this->assertCount(2, $decoded);
        $this->assertSame('a', $decoded[0]['field']);
        $this->assertSame('b', $decoded[1]['field']);
    }

    #[Test]
    public function failure_has_violations() : void
    {
        $violations = DataTransferViolations::from([
                                                       new DataTransferViolation(field: 'email', message: 'Invalid.'),
                                                   ]);

        $failure = new DataTransferFailure(
            message   : 'Validation failed.',
            violations: $violations,
        );

        $this->assertNotNull($failure->violations);
        $this->assertSame(1, $failure->violations->count());
        $this->assertSame('Validation failed.', $failure->getMessage());
    }

    #[Test]
    public function failure_can_have_previous_exception() : void
    {
        $previous = new RuntimeException('Root cause.');

        $failure = new DataTransferFailure(
            message : 'Transfer failed.',
            previous: $previous,
        );

        $this->assertSame($previous, $failure->getPrevious());
    }

    #[Test]
    public function failure_extends_runtime_exception() : void
    {
        $failure = new DataTransferFailure('error');

        $this->assertInstanceOf(RuntimeException::class, $failure);
    }

    #[Test]
    public function result_success_returns_success() : void
    {
        $object = new stdClass();
        $result = DataTransferResult::success($object);

        $this->assertTrue($result->isSuccess());
        $this->assertFalse($result->isFailure());
        $this->assertFalse($result->hasViolations());
        $this->assertSame($object, $result->object());
    }

    #[Test]
    public function result_failure_returns_failure() : void
    {
        $failure = new DataTransferFailure(
            message   : 'Failed.',
            violations: DataTransferViolations::from([
                                                         new DataTransferViolation(field: 'name', message: 'Required.'),
                                                     ]),
        );

        $result = DataTransferResult::failure($failure);

        $this->assertFalse($result->isSuccess());
        $this->assertTrue($result->isFailure());
        $this->assertTrue($result->hasViolations());
    }

    #[Test]
    public function result_violations_returns_violations_on_failure() : void
    {
        $failure = new DataTransferFailure(
            message   : 'Failed.',
            violations: DataTransferViolations::from([
                                                         new DataTransferViolation(field: 'email', message: 'Invalid.'),
                                                     ]),
        );

        $result = DataTransferResult::failure($failure);

        $violations = $result->violations();
        $this->assertFalse($violations->isEmpty());
        $this->assertSame(1, $violations->count());
    }

    #[Test]
    public function result_violations_returns_empty_on_success() : void
    {
        $result = DataTransferResult::success(new stdClass());

        $violations = $result->violations();
        $this->assertTrue($violations->isEmpty());
    }

    #[Test]
    public function result_object_throws_on_failure() : void
    {
        $result = DataTransferResult::failure(new DataTransferFailure('Failed.'));

        $this->expectException(DataTransferException::class);
        $result->object();
    }

    #[Test]
    public function result_failure_reason_throws_on_success() : void
    {
        $result = DataTransferResult::success(new stdClass());

        $this->expectException(DataTransferException::class);
        $result->failureReason();
    }

    #[Test]
    public function result_object_message_is_helpful() : void
    {
        $result = DataTransferResult::failure(new DataTransferFailure('Failed.'));

        try {
            $result->object();
            $this->fail('Expected DataTransferException');
        } catch (DataTransferException $e) {
            $this->assertStringContainsString('violations', $e->getMessage());
        }
    }

    #[Test]
    public function result_failure_reason_message_is_helpful() : void
    {
        $result = DataTransferResult::success(new stdClass());

        try {
            $result->failureReason();
            $this->fail('Expected DataTransferException');
        } catch (DataTransferException $e) {
            $this->assertStringContainsString('successfully', $e->getMessage());
        }
    }

    #[Test]
    public function failure_without_violations_has_empty_violations() : void
    {
        $failure = new DataTransferFailure('Generic failure.');

        $result = DataTransferResult::failure($failure);

        $this->assertFalse($result->hasViolations());
        $this->assertTrue($result->violations()->isEmpty());
    }

    #[Test]
    public function violation_with_null_invalid_value_serializes_correctly() : void
    {
        $violation = new DataTransferViolation(
            field       : 'token',
            message     : 'Cannot be null.',
            invalidValue: null,
        );

        $this->assertNull($violation->invalidValue);
        $this->assertNull($violation->code);
    }

    #[Test]
    public function violations_is_readonly() : void
    {
        $reflection = new ReflectionClass(DataTransferViolations::class);

        $this->assertTrue($reflection->isReadOnly());
    }

    #[Test]
    public function violation_is_readonly() : void
    {
        $reflection = new ReflectionClass(DataTransferViolation::class);

        $this->assertTrue($reflection->isReadOnly());
    }

    #[Test]
    public function result_is_readonly() : void
    {
        $reflection = new ReflectionClass(DataTransferResult::class);

        $this->assertTrue($reflection->isReadOnly());
    }

    #[Test]
    public function exception_extends_invalid_argument_exception() : void
    {
        $exception = new DataTransferException('test');

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }
}
