<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\SecureRequest;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\MapFrom;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Max;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Min;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\RegexPattern;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\StringType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferViolations;
use Avax\Components\HTTP\SecureRequest\System\Capabilities\SecureRequestValidation\ValidationContext;
use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestAuthorizationFailed;
use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestValidationFailed;
use Avax\Components\HTTP\SecureRequest\System\PublicSurface\SecureRequest;
use PHPUnit\Framework\TestCase;

final class SecureRequestCapabilitiesTest extends TestCase
{
    public function test_secure_request_resolved_successfully() : void
    {
        $request = new TestRegisterRequest();
        $request->runLifecycle([
                                   'email'    => 'user@example.com',
                                   'username' => 'avax',
                                   'password' => 'Secure1!',
                               ]);

        $this->assertSame('user@example.com', $request->email);
        $this->assertSame('avax', $request->username);
        $this->assertSame('Secure1!', $request->password);
    }

    public function test_validation_failure_stops_execution() : void
    {
        $this->expectException(SecureRequestValidationFailed::class);

        $request = new TestRegisterRequest();
        $request->runLifecycle([
                                   'email'    => 'invalid',
                                   'username' => 'ab',
                                   'password' => 'weak',
                               ]);
    }

    public function test_authorization_failure_stops_execution() : void
    {
        $this->expectException(SecureRequestAuthorizationFailed::class);

        $request = new TestAdminRequest();
        $request->runLifecycle([
                                   'email' => 'user@example.com',
                               ]);
    }

    public function test_lifecycle_hooks_run_in_correct_order() : void
    {
        $request = new TestHookedRequest();
        $request->runLifecycle(['name' => 'test']);

        $this->assertSame([
                              'beforeHydration',
                              'afterHydration',
                              'beforeValidation',
                              'afterValidation',
                              'passedValidation',
                          ], $request->hookOrder);
    }

    public function test_failed_validation_hook_runs() : void
    {
        $request = new TestFailedHookedRequest();

        try {
            $request->runLifecycle([]);
        } catch (SecureRequestValidationFailed) {
            // Expected
        }

        $this->assertTrue($request->failedValidationCalled);
        $this->assertInstanceOf(DataTransferViolations::class, $request->capturedViolations);
    }

    public function test_custom_validation_hook_receives_context() : void
    {
        $request = new TestCustomValidationRequest();
        $request->runLifecycle(['name' => 'test']);

        $this->assertTrue($request->validationContextReceived);
    }

    public function test_custom_validation_can_add_violation() : void
    {
        $this->expectException(SecureRequestValidationFailed::class);

        $request = new TestPasswordMatchRequest();
        $request->runLifecycle([
                                   'password' => 'secret123',
                                   'username' => 'secret123',
                               ]);
    }

    public function test_get_input_returns_hydrated_data() : void
    {
        $request = new TestRegisterRequest();
        $input   = [
            'email'    => 'user@example.com',
            'username' => 'avax',
            'password' => 'Secure1!',
        ];
        $request->runLifecycle($input);

        $this->assertSame($input, $request->getInput());
    }

    public function test_map_from_works() : void
    {
        $request = new TestMappedRequest();
        $request->runLifecycle(['full_name' => 'AvaX']);

        $this->assertSame('AvaX', $request->name);
    }

    public function test_passed_validation_only_runs_on_success() : void
    {
        $request = new TestHookedRequest();

        try {
            $request->runLifecycle([]);
        } catch (SecureRequestValidationFailed) {
            // Expected
        }

        $this->assertNotContains('passedValidation', $request->hookOrder);
        $this->assertContains('failedValidation', $request->hookOrder);
    }

    public function test_after_validation_only_runs_on_success() : void
    {
        $request = new TestHookedRequest();

        try {
            $request->runLifecycle([]);
        } catch (SecureRequestValidationFailed) {
            // Expected
        }

        $this->assertNotContains('afterValidation', $request->hookOrder);
    }
}

// -- Test Requests --

final class TestRegisterRequest extends SecureRequest
{
    #[Required]
    #[StringType]
    public string $email;

    #[Required]
    #[StringType]
    #[Min(3)]
    #[Max(50)]
    public string $username;

    #[Required]
    #[StringType]
    #[Min(8)]
    #[Max(64)]
    #[RegexPattern('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&]).{8,64}$/')]
    public string $password;

    public function authorize() : bool
    {
        return true;
    }
}

final class TestAdminRequest extends SecureRequest
{
    #[Required]
    #[StringType]
    public string $email;

    public function authorize() : bool
    {
        return false;
    }
}

final class TestHookedRequest extends SecureRequest
{
    #[Required]
    public string $name;

    /** @var list<string> */
    public array $hookOrder = [];

    public function authorize() : bool
    {
        return true;
    }

    protected function beforeHydration() : void
    {
        $this->hookOrder[] = 'beforeHydration';
    }

    protected function afterHydration() : void
    {
        $this->hookOrder[] = 'afterHydration';
    }

    protected function beforeValidation() : void
    {
        $this->hookOrder[] = 'beforeValidation';
    }

    protected function afterValidation() : void
    {
        $this->hookOrder[] = 'afterValidation';
    }

    protected function passedValidation() : void
    {
        $this->hookOrder[] = 'passedValidation';
    }

    protected function failedValidation(DataTransferViolations $violations) : void
    {
        $this->hookOrder[] = 'failedValidation';
    }
}

final class TestFailedHookedRequest extends SecureRequest
{
    #[Required]
    public string $name;

    public bool                    $failedValidationCalled = false;
    public ?DataTransferViolations $capturedViolations     = null;

    public function authorize() : bool
    {
        return true;
    }

    protected function failedValidation(DataTransferViolations $violations) : void
    {
        $this->failedValidationCalled = true;
        $this->capturedViolations     = $violations;
    }
}

final class TestCustomValidationRequest extends SecureRequest
{
    #[Required]
    public string $name;

    public bool $validationContextReceived = false;

    public function authorize() : bool
    {
        return true;
    }

    protected function withValidation(ValidationContext $context) : void
    {
        $this->validationContextReceived = true;
    }
}

final class TestPasswordMatchRequest extends SecureRequest
{
    #[Required]
    #[StringType]
    public string $password;

    #[Required]
    #[StringType]
    public string $username;

    public function authorize() : bool
    {
        return true;
    }

    protected function withValidation(ValidationContext $context) : void
    {
        assert($context->request instanceof self);
        if ($context->request->password === $context->request->username) {
            $context->addViolation(
                field  : 'password',
                message: 'Password must not match username.',
            );
        }
    }
}

final class TestMappedRequest extends SecureRequest
{
    #[Required]
    #[MapFrom('full_name')]
    public string $name;

    public function authorize() : bool
    {
        return true;
    }
}
