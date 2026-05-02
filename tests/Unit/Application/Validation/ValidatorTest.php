<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Application\Validation;

use Avax\Components\Application\Validation\System\Capabilities\Rules\Validator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Validator class.
 */
final class ValidatorTest extends TestCase
{
    #[Test]
    public function required_rule_fails_on_empty_value() : void
    {
        $result = Validator::make(
            data : ['name' => ''],
            rules: ['name' => 'required'],
        );

        $this->assertTrue($result->fails());
        $this->assertTrue($result->hasError('name'));
    }

    #[Test]
    public function required_rule_fails_on_null_value() : void
    {
        $result = Validator::make(
            data : ['name' => null],
            rules: ['name' => 'required'],
        );

        $this->assertTrue($result->fails());
    }

    #[Test]
    public function required_rule_passes_on_valid_value() : void
    {
        $result = Validator::make(
            data : ['name' => 'John'],
            rules: ['name' => 'required'],
        );

        $this->assertTrue($result->passes());
    }

    #[Test]
    public function email_rule_validates_email_format() : void
    {
        $result = Validator::make(
            data : ['email' => 'invalid-email'],
            rules: ['email' => 'required|email'],
        );

        $this->assertTrue($result->fails());
        $this->assertTrue($result->hasError('email'));
    }

    #[Test]
    public function email_rule_accepts_valid_email() : void
    {
        $result = Validator::make(
            data : ['email' => 'user@example.com'],
            rules: ['email' => 'required|email'],
        );

        $this->assertTrue($result->passes());
    }

    #[Test]
    public function min_rule_validates_minimum_length() : void
    {
        $result = Validator::make(
            data : ['password' => '123'],
            rules: ['password' => 'required|min:5'],
        );

        $this->assertTrue($result->fails());
        $this->assertTrue($result->hasError('password'));
    }

    #[Test]
    public function min_rule_accepts_value_meeting_minimum() : void
    {
        $result = Validator::make(
            data : ['password' => '12345'],
            rules: ['password' => 'required|min:5'],
        );

        $this->assertTrue($result->passes());
    }

    #[Test]
    public function max_rule_validates_maximum_length() : void
    {
        $result = Validator::make(
            data : ['name' => 'This is a very long name that exceeds the limit'],
            rules: ['name' => 'required|max:10'],
        );

        $this->assertTrue($result->fails());
    }

    #[Test]
    public function max_rule_accepts_value_within_limit() : void
    {
        $result = Validator::make(
            data : ['name' => 'Short'],
            rules: ['name' => 'required|max:10'],
        );

        $this->assertTrue($result->passes());
    }

    #[Test]
    public function multiple_rules_combined() : void
    {
        $result = Validator::make(
            data : [
                       'name'     => '',
                       'email'    => 'invalid',
                       'password' => '12',
                   ],
            rules: [
                       'name'     => 'required|min:3',
                       'email'    => 'required|email',
                       'password' => 'required|min:6',
                   ],
        );

        $this->assertTrue($result->fails());
        $this->assertTrue($result->hasError('name'));
        $this->assertTrue($result->hasError('email'));
        $this->assertTrue($result->hasError('password'));
    }

    #[Test]
    public function all_rules_pass_with_valid_data() : void
    {
        $result = Validator::make(
            data : [
                       'name'     => 'John Doe',
                       'email'    => 'john@example.com',
                       'password' => 'secure123',
                   ],
            rules: [
                       'name'     => 'required|min:3|max:50',
                       'email'    => 'required|email|max:255',
                       'password' => 'required|min:6',
                   ],
        );

        $this->assertTrue($result->passes());
        $this->assertEmpty($result->all());
    }

    #[Test]
    public function get_error_returns_first_error_for_field() : void
    {
        $result = Validator::make(
            data : ['email' => ''],
            rules: ['email' => 'required|email'],
        );

        $error = $result->getError('email');
        $this->assertNotNull($error);
        $this->assertStringContainsString('required', $error);
    }

    #[Test]
    public function get_errors_returns_all_errors_for_field() : void
    {
        $result = Validator::make(
            data : ['name' => ''],
            rules: ['name' => 'required|min:3'],
        );

        $errors = $result->getErrors('name');
        $this->assertIsArray($errors);
        $this->assertGreaterThanOrEqual(1, count($errors));
    }

    #[Test]
    public function first_returns_first_error_overall() : void
    {
        $result = Validator::make(
            data : ['email' => '', 'name' => ''],
            rules: ['email' => 'required', 'name' => 'required'],
        );

        $first = $result->first();
        $this->assertNotNull($first);
        $this->assertIsString($first);
    }

    #[Test]
    public function integer_rule_validates_integer_values() : void
    {
        $result = Validator::make(
            data : ['age' => 'not-a-number'],
            rules: ['age' => 'required|integer'],
        );

        $this->assertTrue($result->fails());
    }

    #[Test]
    public function integer_rule_accepts_valid_integers() : void
    {
        $result = Validator::make(
            data : ['age' => '25'],
            rules: ['age' => 'required|integer'],
        );

        $this->assertTrue($result->passes());
    }

    #[Test]
    public function array_rule_validates_array_values() : void
    {
        $result = Validator::make(
            data : ['tags' => 'not-an-array'],
            rules: ['tags' => 'array'],
        );

        $this->assertTrue($result->fails());
    }

    #[Test]
    public function in_rule_validates_allowed_values() : void
    {
        $result = Validator::make(
            data : ['status' => 'invalid'],
            rules: ['status' => 'in:active,inactive,pending'],
        );

        $this->assertTrue($result->fails());
    }

    #[Test]
    public function in_rule_accepts_valid_value() : void
    {
        $result = Validator::make(
            data : ['status' => 'active'],
            rules: ['status' => 'in:active,inactive,pending'],
        );

        $this->assertTrue($result->passes());
    }

    #[Test]
    public function rules_as_array_works_correctly() : void
    {
        $result = Validator::make(
            data : ['email' => 'user@example.com'],
            rules: ['email' => ['required', 'email', 'max:255']],
        );

        $this->assertTrue($result->passes());
    }

    #[Test]
    public function custom_error_messages_are_used() : void
    {
        $result = Validator::make(
            data    : ['email' => ''],
            rules   : ['email' => 'required'],
            messages: ['email.required' => 'Please provide your email address'],
        );

        $error = $result->getError('email');
        $this->assertEquals('Please provide your email address', $error);
    }

    #[Test]
    public function nullable_allows_empty_value() : void
    {
        $result = Validator::make(
            data : ['optional' => ''],
            rules: ['optional' => 'nullable|string'],
        );

        $this->assertTrue($result->passes());
    }

    #[Test]
    public function url_rule_validates_url_format() : void
    {
        $result = Validator::make(
            data : ['website' => 'not-a-url'],
            rules: ['website' => 'url'],
        );

        $this->assertTrue($result->fails());
    }

    #[Test]
    public function url_rule_accepts_valid_url() : void
    {
        $result = Validator::make(
            data : ['website' => 'https://example.com'],
            rules: ['website' => 'url'],
        );

        $this->assertTrue($result->passes());
    }

    #[Test]
    public function uuid_rule_validates_uuid_format() : void
    {
        $result = Validator::make(
            data : ['id' => 'not-a-uuid'],
            rules: ['id' => 'uuid'],
        );

        $this->assertTrue($result->fails());
    }

    #[Test]
    public function uuid_rule_accepts_valid_uuid() : void
    {
        $result = Validator::make(
            data : ['id' => '550e8400-e29b-41d4-a716-446655440000'],
            rules: ['id' => 'uuid'],
        );

        $this->assertTrue($result->passes());
    }

    #[Test]
    public function between_rule_validates_range() : void
    {
        $result = Validator::make(
            data : ['age' => '5'],
            rules: ['age' => 'between:18,65'],
        );

        $this->assertTrue($result->fails());
    }

    #[Test]
    public function between_rule_accepts_value_in_range() : void
    {
        $result = Validator::make(
            data : ['age' => 30],
            rules: ['age' => 'between:18,65'],
        );

        $this->assertTrue($result->passes());
    }

    #[Test]
    public function date_rule_validates_date_format() : void
    {
        $result = Validator::make(
            data : ['birthday' => 'not-a-date'],
            rules: ['birthday' => 'date'],
        );

        $this->assertTrue($result->fails());
    }

    #[Test]
    public function date_rule_accepts_valid_date() : void
    {
        $result = Validator::make(
            data : ['birthday' => '1990-01-15'],
            rules: ['birthday' => 'date'],
        );

        $this->assertTrue($result->passes());
    }

    #[Test]
    public function boolean_rule_validates_boolean_values() : void
    {
        $result = Validator::make(
            data : ['active' => 'maybe'],
            rules: ['active' => 'boolean'],
        );

        $this->assertTrue($result->fails());
    }

    #[Test]
    public function boolean_rule_accepts_valid_booleans() : void
    {
        $result = Validator::make(
            data : ['active' => '1'],
            rules: ['active' => 'boolean'],
        );

        $this->assertTrue($result->passes());
    }
}
