<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Validation;

use Avax\Components\Application\Validation\System\PublicSurface\Validation;
use Avax\Components\Application\Validation\System\PublicSurface\ValidationResult;
use PHPUnit\Framework\TestCase;

final class ValidationCapabilitiesTest extends TestCase
{
    private Validation $validation;

    public function test_it_passes_valid_data() : void
    {
        $data  = ['email' => 'test@example.com', 'age' => 25];
        $rules = ['email' => 'required|email', 'age' => 'required|integer|min:18'];

        $result = $this->validation->validate($data, $rules);

        $this->assertTrue($result->passes());
        $this->assertEmpty($result->errors);
    }

    public function test_it_fails_invalid_data() : void
    {
        $data  = ['email' => 'invalid-email', 'age' => 15];
        $rules = ['email' => 'required|email', 'age' => 'required|integer|min:18'];

        $result = $this->validation->validate($data, $rules);

        $this->assertFalse($result->passes());
        $this->assertArrayHasKey('email', $result->errors);
        $this->assertArrayHasKey('age', $result->errors);
    }

    public function test_it_returns_custom_error_messages() : void
    {
        $data     = ['name' => ''];
        $rules    = ['name' => 'required'];
        $messages = ['name.required' => 'Please provide your name.'];

        $result = $this->validation->validate($data, $rules, $messages);

        $this->assertFalse($result->passes());
        $this->assertSame('Please provide your name.', $result->errors['name'][0]);
    }

    public function test_it_handles_nested_data() : void
    {
        $data  = ['user' => ['profile' => ['age' => 10]]];
        $rules = ['user.profile.age' => 'required|integer|min:18'];

        $result = $this->validation->validate($data, $rules);

        $this->assertFalse($result->passes());
        $this->assertArrayHasKey('user.profile.age', $result->errors);
    }

    public function test_it_supports_nullable_fields() : void
    {
        $data  = ['email' => null];
        $rules = ['email' => 'nullable|email'];

        $result = $this->validation->validate($data, $rules);

        $this->assertTrue($result->passes());
    }

    protected function setUp() : void
    {
        $this->validation = new Validation();
    }
}
