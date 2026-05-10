<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\API\SchemaGeneration;

use Avax\Components\API\SchemaGeneration\System\Foundation\PayloadValidationResult;
use PHPUnit\Framework\TestCase;

final class PayloadValidationResultTest extends TestCase
{
    public function test_valid_factory_produces_valid_result() : void
    {
        $result = PayloadValidationResult::valid();

        $this->assertTrue($result->valid);
        $this->assertEmpty($result->errors);
    }

    public function test_valid_result_has_empty_errors() : void
    {
        $result = PayloadValidationResult::valid();

        $this->assertSame([], $result->errors);
    }

    public function test_invalid_factory_produces_invalid_result() : void
    {
        $result = PayloadValidationResult::invalid(errors: ['Field is required.']);

        $this->assertFalse($result->valid);
    }

    public function test_invalid_result_stores_errors() : void
    {
        $errors = ['Field is required.', 'Field must be a string.'];
        $result = PayloadValidationResult::invalid(errors: $errors);

        $this->assertSame($errors, $result->errors);
    }

    public function test_invalid_result_can_have_single_error() : void
    {
        $result = PayloadValidationResult::invalid(errors: ['Only one error.']);

        $this->assertCount(1, $result->errors);
        $this->assertSame('Only one error.', $result->errors[0]);
    }

    public function test_invalid_result_can_have_multiple_errors() : void
    {
        $result = PayloadValidationResult::invalid(errors: [
                                                               "Required field 'name' is missing.",
                                                               "Required field 'email' is missing.",
                                                               "Field 'age' must be of type integer.",
                                                           ]);

        $this->assertCount(3, $result->errors);
    }

    public function test_constructor_with_valid_true_and_no_errors() : void
    {
        $result = new PayloadValidationResult(valid: true, errors: []);

        $this->assertTrue($result->valid);
        $this->assertEmpty($result->errors);
    }

    public function test_constructor_with_valid_true_and_errors() : void
    {
        $result = new PayloadValidationResult(valid: true, errors: ['Some error']);

        $this->assertTrue($result->valid);
        $this->assertSame(['Some error'], $result->errors);
    }

    public function test_constructor_with_valid_false_and_errors() : void
    {
        $result = new PayloadValidationResult(valid: false, errors: ['Error 1', 'Error 2']);

        $this->assertFalse($result->valid);
        $this->assertCount(2, $result->errors);
    }

    public function test_constructor_with_valid_false_and_empty_errors() : void
    {
        $result = new PayloadValidationResult(valid: false, errors: []);

        $this->assertFalse($result->valid);
        $this->assertEmpty($result->errors);
    }

    public function test_valid_property_is_publicly_accessible() : void
    {
        $result = PayloadValidationResult::valid();

        $this->assertTrue($result->valid);
    }

    public function test_errors_property_is_publicly_accessible() : void
    {
        $result = PayloadValidationResult::invalid(errors: ['test']);

        $this->assertIsArray($result->errors);
    }

    public function test_result_is_readonly() : void
    {
        $result = PayloadValidationResult::valid();

        // readonly class - properties are immutable
        $this->assertObjectHasProperty('valid', $result);
        $this->assertObjectHasProperty('errors', $result);
    }

    public function test_valid_and_invalid_are_distinct() : void
    {
        $valid   = PayloadValidationResult::valid();
        $invalid = PayloadValidationResult::invalid(errors: ['error']);

        $this->assertNotSame($valid->valid, $invalid->valid);
    }

    public function test_empty_errors_list_still_valid_when_valid_is_true() : void
    {
        $result = new PayloadValidationResult(valid: true, errors: []);

        $this->assertTrue($result->valid);
    }

    public function test_errors_preserve_order() : void
    {
        $errors = [
            'First error',
            'Second error',
            'Third error',
        ];
        $result = PayloadValidationResult::invalid(errors: $errors);

        $this->assertSame('First error', $result->errors[0]);
        $this->assertSame('Second error', $result->errors[1]);
        $this->assertSame('Third error', $result->errors[2]);
    }

    public function test_valid_result_equality() : void
    {
        $result1 = PayloadValidationResult::valid();
        $result2 = PayloadValidationResult::valid();

        $this->assertSame($result1->valid, $result2->valid);
        $this->assertSame($result1->errors, $result2->errors);
    }

    public function test_invalid_result_with_same_errors_are_equal() : void
    {
        $errors  = ['Field required'];
        $result1 = PayloadValidationResult::invalid(errors: $errors);
        $result2 = PayloadValidationResult::invalid(errors: $errors);

        $this->assertSame($result1->valid, $result2->valid);
        $this->assertSame($result1->errors, $result2->errors);
    }

    public function test_can_be_used_in_conditional_logic() : void
    {
        $result = PayloadValidationResult::valid();

        if ($result->valid) {
            $status = 'passed';
        } else {
            $status = 'failed';
        }

        $this->assertSame('passed', $status);
    }

    public function test_can_iterate_over_errors() : void
    {
        $result = PayloadValidationResult::invalid(errors: [
                                                               'Error A',
                                                               'Error B',
                                                               'Error C',
                                                           ]);

        $collected = [];
        foreach ($result->errors as $error) {
            $collected[] = $error;
        }

        $this->assertCount(3, $collected);
        $this->assertSame('Error A', $collected[0]);
        $this->assertSame('Error B', $collected[1]);
        $this->assertSame('Error C', $collected[2]);
    }

    public function test_errors_can_be_mapped() : void
    {
        $result = PayloadValidationResult::invalid(errors: [
                                                               "Field 'name' is required.",
                                                               "Field 'email' is invalid.",
                                                           ]);

        $messages = array_map(
            callback: static fn (string $error) : string => strtoupper($error),
            array   : $result->errors,
        );

        $this->assertSame("FIELD 'NAME' IS REQUIRED.", $messages[0]);
        $this->assertSame("FIELD 'EMAIL' IS INVALID.", $messages[1]);
    }

    public function test_errors_can_be_filtered() : void
    {
        $result = PayloadValidationResult::invalid(errors: [
                                                               "Field 'name' is required.",
                                                               "Field 'name' must be a string.",
                                                               "Field 'email' is required.",
                                                           ]);

        $nameErrors = array_filter(
            array   : $result->errors,
            callback: static fn (string $error) : bool => str_contains($error, "'name'"),
        );

        $this->assertCount(2, $nameErrors);
    }

    public function test_errors_can_be_imploded_for_display() : void
    {
        $result = PayloadValidationResult::invalid(errors: [
                                                               'Error 1',
                                                               'Error 2',
                                                               'Error 3',
                                                           ]);

        $display = implode(separator: '; ', array: $result->errors);

        $this->assertSame('Error 1; Error 2; Error 3', $display);
    }

    public function test_valid_result_can_be_used_as_success_indicator() : void
    {
        $result = PayloadValidationResult::valid();

        $this->assertTrue($result->valid === true);
        $this->assertFalse($result->valid === false);
    }

    public function test_invalid_result_can_be_used_as_failure_indicator() : void
    {
        $result = PayloadValidationResult::invalid(errors: ['failed']);

        $this->assertTrue($result->valid === false);
        $this->assertFalse($result->valid === true);
    }
}
