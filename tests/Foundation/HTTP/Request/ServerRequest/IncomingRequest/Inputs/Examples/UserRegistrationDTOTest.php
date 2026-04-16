<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Request\Inputs\Examples;

use Avax\DataHandling\ObjectHandling\DTO\DTOValidationException;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\Inputs\Examples\UserRegistrationDTO;
use PHPUnit\Framework\TestCase;

final class UserRegistrationDTOTest extends TestCase
{
    public function test_valid_data_hydrates_successfully(): void
    {
        $dto = new UserRegistrationDTO(data: [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123',
            'age' => 25,
        ]);

        $this->assertSame('John Doe', $dto->name);
        $this->assertSame('john@example.com', $dto->email);
        $this->assertSame('SecurePass123', $dto->password);
        $this->assertSame(25, $dto->age);
    }

    public function test_validates_email_format(): void
    {
        $this->expectException(DTOValidationException::class);

        new UserRegistrationDTO(data: [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'SecurePass123',
        ]);
    }

    public function test_validates_password_min_length(): void
    {
        $this->expectException(DTOValidationException::class);

        new UserRegistrationDTO(data: [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'short',
        ]);
    }

    public function test_validates_password_complexity(): void
    {
        $this->expectException(DTOValidationException::class);

        new UserRegistrationDTO(data: [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'alllowercase123',
        ]);
    }

    public function test_validates_age_minimum(): void
    {
        $this->expectException(DTOValidationException::class);

        new UserRegistrationDTO(data: [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123',
            'age' => 15,
        ]);
    }

    public function test_optional_phone_is_nullable(): void
    {
        $dto = new UserRegistrationDTO(data: [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123',
        ]);

        $this->assertNull($dto->phone);
    }

    public function test_age_has_default_value(): void
    {
        $dto = new UserRegistrationDTO(data: [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123',
        ]);

        $this->assertSame(18, $dto->age);
    }

    public function test_collects_all_validation_errors(): void
    {
        try {
            new UserRegistrationDTO(data: [
                'name' => 'J',
                'email' => 'invalid',
                'password' => 'x',
            ]);
            $this->fail('Expected DTOValidationException');
        } catch (DTOValidationException $e) {
            $errors = $e->getErrors();
            $this->assertArrayHasKey('name', $errors);
            $this->assertArrayHasKey('email', $errors);
            $this->assertArrayHasKey('password', $errors);
        }
    }
}
