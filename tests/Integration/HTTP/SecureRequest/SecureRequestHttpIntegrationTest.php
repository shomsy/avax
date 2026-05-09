<?php

declare(strict_types=1);

namespace Avax\Tests\Integration\HTTP\SecureRequest;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Email;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\IntegerType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\MapFrom;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Max;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Min;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\RegexPattern;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\StringType;
use Avax\Components\HTTP\Dispatcher\System\Capabilities\ArgumentResolution\ArgumentResolver;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\Flows\CreateJsonResponse\CreateJsonResponse;
use Avax\Components\HTTP\SecureRequest\System\Capabilities\SecureRequestValidation\ValidationContext;
use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestAuthorizationFailed;
use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestResolutionFailed;
use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestValidationFailed;
use Avax\Components\HTTP\SecureRequest\System\PublicSurface\SecureRequest;
use Avax\Components\HTTP\System\Flows\HandleRequest\CatchUnhandledExceptions;
use Avax\Components\HTTP\System\Flows\HandleRequest\ReportExceptionToLogger;
use GuzzleHttp\Psr7\UploadedFile;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;
use ReflectionMethod;

/**
 * Marker interface to satisfy CatchUnhandledExceptions type hint.
 */
interface RequestInterfaceForCatch extends RequestInterface {}

// -- Integration test SecureRequest classes --

/**
 * HTTP integration tests proving SecureRequest + DataTransfer delegation
 * and exception rendering through the full HTTP pipeline.
 */
final class SecureRequestHttpIntegrationTest extends TestCase
{
    private ArgumentResolver         $resolver;
    private CatchUnhandledExceptions $exceptionHandler;

    public function test_valid_secure_request_reaches_controller() : void
    {
        $controller = new class {
            public function __invoke(TestIntegrationRegisterRequest $request) : string
            {
                return $request->email . '|' . $request->username;
            }
        };

        $serverRequest = $this->createServerRequest(
            method: 'POST',
            body  : ['email' => 'user@example.com', 'username' => 'avax', 'password' => 'Secure1!'],
        );

        $arguments = $this->resolver->resolve(
            new ReflectionMethod($controller, '__invoke'),
            $serverRequest,
        );

        // Controller invoked successfully
        $result = $controller->__invoke(...$arguments);
        $this->assertSame('user@example.com|avax', $result);
    }

    // -- 1. Valid SecureRequest reaches controller --

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, mixed>|null $query
     * @param array<string, mixed>|null $headers
     */
    private function createServerRequest(
        string $method = 'GET',
        ?array $body = null,
        ?array $query = null,
        ?array $headers = null,
    ) : ServerRequest
    {
        return new ServerRequest(
            method     : $method,
            parsedBody : $body,
            queryParams: $query ?? [],
            headers    : $headers ?? [],
        );
    }

    // -- 2. Invalid SecureRequest returns HTTP 422 --

    public function test_invalid_secure_request_returns_422() : void
    {
        $controller = new class {
            public function __invoke(TestIntegrationRegisterRequest $request) : string
            {
                return 'should not reach';
            }
        };

        $serverRequest = $this->createServerRequest(
            method: 'POST',
            body  : ['email' => 'not-email', 'username' => 'ab', 'password' => 'weak'],
        );

        $request = new class extends ServerRequest implements RequestInterfaceForCatch {
            public function __construct() { parent::__construct(); }
        };

        try {
            $this->resolver->resolve(
                new ReflectionMethod($controller, '__invoke'),
                $serverRequest,
            );
            $this->fail('Expected SecureRequestValidationFailed');
        } catch (SecureRequestValidationFailed $e) {
            $response = $this->exceptionHandler->handle($e, $request);

            $this->assertSame(422, $response->getStatusCode());
            $body = (string) $response->getBody();
            $data = json_decode($body, true);
            $this->assertSame('The given data was invalid.', $data['message']);
            $this->assertArrayHasKey('errors', $data);
            $this->assertNotEmpty($data['errors']);
        }
    }

    // -- 3. Controller NOT invoked on validation failure --

    public function test_controller_not_invoked_on_validation_failure() : void
    {
        $controller = new class {
            public static bool $invoked = false;

            public function __invoke(TestIntegrationRegisterRequest $request) : string
            {
                self::$invoked = true;

                return 'never';
            }
        };

        $serverRequest = $this->createServerRequest(
            method: 'POST',
            body  : ['email' => 'bad', 'username' => 'ab', 'password' => 'weak'],
        );

        try {
            $this->resolver->resolve(
                new ReflectionMethod($controller, '__invoke'),
                $serverRequest,
            );
        } catch (SecureRequestValidationFailed) {
            // Expected
        }

        $this->assertFalse($controller::$invoked);
    }

    // -- 4. Unauthorized SecureRequest returns HTTP 403 --

    public function test_unauthorized_secure_request_returns_403() : void
    {
        $controller = new class {
            public function __invoke(TestIntegrationAdminRequest $request) : string
            {
                return 'admin';
            }
        };

        $serverRequest = $this->createServerRequest(
            method: 'POST',
            body  : ['email' => 'user@example.com'],
        );

        $request = new class extends ServerRequest implements RequestInterfaceForCatch {
            public function __construct() { parent::__construct(); }
        };

        try {
            $this->resolver->resolve(
                new ReflectionMethod($controller, '__invoke'),
                $serverRequest,
            );
            $this->fail('Expected SecureRequestAuthorizationFailed');
        } catch (SecureRequestAuthorizationFailed $e) {
            $response = $this->exceptionHandler->handle($e, $request);

            $this->assertSame(403, $response->getStatusCode());
            $body = (string) $response->getBody();
            $data = json_decode($body, true);
            $this->assertSame('This action is not authorized.', $data['message']);
        }
    }

    // -- 5. Controller NOT invoked on authorization failure --

    public function test_controller_not_invoked_on_authorization_failure() : void
    {
        $controller = new class {
            public static bool $invoked = false;

            public function __invoke(TestIntegrationAdminRequest $request) : string
            {
                self::$invoked = true;

                return 'should not reach';
            }
        };

        $serverRequest = $this->createServerRequest(
            method: 'POST',
            body  : ['email' => 'user@example.com'],
        );

        try {
            $this->resolver->resolve(
                new ReflectionMethod($controller, '__invoke'),
                $serverRequest,
            );
            $this->fail('Expected SecureRequestAuthorizationFailed');
        } catch (SecureRequestAuthorizationFailed) {
            // Expected - controller was never invoked
        }

        $this->assertFalse($controller::$invoked, 'Controller must not be invoked on authorization failure');
    }

    // -- 6. Resolution failure returns 500 --

    public function test_resolution_failure_returns_500() : void
    {
        $request = new class extends ServerRequest implements RequestInterfaceForCatch {
            public function __construct() { parent::__construct(); }
        };

        $exception = new SecureRequestResolutionFailed('Cannot resolve request.');
        $response  = $this->exceptionHandler->handle($exception, $request);

        $this->assertSame(500, $response->getStatusCode());
        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        $this->assertSame('Internal Server Error', $data['error']);
        $this->assertSame('Cannot resolve request.', $data['message']);
    }

    // -- 7. JSON body input hydrates SecureRequest --

    public function test_json_body_hydrates_secure_request() : void
    {
        $controller = new class {
            public function __invoke(TestIntegrationRegisterRequest $request) : string
            {
                return $request->email . '|' . $request->username;
            }
        };

        $jsonBody = json_encode([
                                    'email'    => 'json@example.com',
                                    'username' => 'jsonuser',
                                    'password' => 'Secure1!',
                                ]);

        $serverRequest = new ServerRequest(
            method : 'POST',
            headers: ['Content-Type' => 'application/json'],
            stream : Utils::streamFor($jsonBody),
        );

        $arguments = $this->resolver->resolve(
            new ReflectionMethod($controller, '__invoke'),
            $serverRequest,
        );

        $result = $controller->__invoke(...$arguments);
        $this->assertSame('json@example.com|jsonuser', $result);
    }

    // -- 8. Route params override body/query --

    public function test_route_params_override_body_and_query() : void
    {
        $controller = new class {
            public function __invoke(TestIntegrationRegisterRequest $request) : string
            {
                return $request->username;
            }
        };

        // Query says 'queryuser', body says 'bodyuser', route says 'routeuser'
        $serverRequest = new ServerRequest(
            method     : 'POST',
            queryParams: ['email' => 'route@example.com', 'username' => 'queryuser'],
            parsedBody : ['email' => 'route@example.com', 'username' => 'bodyuser', 'password' => 'Secure1!'],
        );
        // Route params set via withAttribute
        $serverRequest = $serverRequest->withAttribute('username', 'routeuser');

        $arguments = $this->resolver->resolve(
            new ReflectionMethod($controller, '__invoke'),
            $serverRequest,
        );

        $result = $controller->__invoke(...$arguments);
        $this->assertSame('routeuser', $result);
    }

    // -- 9. Uploaded file input is included --

    public function test_uploaded_file_input_is_included() : void
    {
        $controller = new class {
            public function __invoke(TestIntegrationFileRequest $request) : string
            {
                return $request->document instanceof UploadedFileInterface ? 'has-file' : 'no-file';
            }
        };

        $uploadedFile = new UploadedFile(
            Utils::streamFor('file content'),
            12,
            UPLOAD_ERR_OK,
            'test.txt',
            'text/plain',
        );

        $serverRequest = new ServerRequest(
            method       : 'POST',
            parsedBody   : ['title' => 'Test Document'],
            uploadedFiles: ['document' => $uploadedFile],
        );

        $arguments = $this->resolver->resolve(
            new ReflectionMethod($controller, '__invoke'),
            $serverRequest,
        );

        $result = $controller->__invoke(...$arguments);
        $this->assertSame('has-file', $result);
    }

    // -- 10. SecureRequest delegation proof --

    public function test_secure_request_has_no_hydrate_properties() : void
    {
        $class = new ReflectionClass(SecureRequest::class);
        $this->assertFalse(
            $class->hasMethod('hydrateProperties'),
            'SecureRequest must not have hydrateProperties() — hydration delegated to DataTransfer',
        );
    }

    public function test_secure_request_has_no_validate_attributes() : void
    {
        $class = new ReflectionClass(SecureRequest::class);
        $this->assertFalse(
            $class->hasMethod('validateAttributes'),
            'SecureRequest must not have validateAttributes() — validation delegated to DataTransfer',
        );
    }

    public function test_secure_request_delegates_to_create_data_object() : void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../../components/HTTP/SecureRequest/System/PublicSurface/SecureRequest.php',
        );
        $this->assertIsString($source);

        $this->assertStringContainsString(
            'CreateDataObject',
            $source,
            'SecureRequest must reference CreateDataObject for delegation',
        );
        $this->assertStringContainsString(
            'hydrateInto',
            $source,
            'SecureRequest must call hydrateInto() for delegation',
        );
    }

    // -- 11. Dependency boundary proof --

    public function test_data_stack_has_no_data_transfer_imports() : void
    {
        $output = [];
        exec('grep -R "^use.*DataTransfer" ' . escapeshellarg(__DIR__ . '/../../../../components/DataStack/Data') . ' -n --include="*.php" 2>/dev/null', $output);
        $this->assertEmpty($output, 'DataStack/Data must not import DataTransfer');
    }

    public function test_data_stack_has_no_secure_request_imports() : void
    {
        $output = [];
        exec('grep -R "^use.*SecureRequest" ' . escapeshellarg(__DIR__ . '/../../../../components/DataStack/Data') . ' -n --include="*.php" 2>/dev/null', $output);
        $this->assertEmpty($output, 'DataStack/Data must not import SecureRequest');
    }

    public function test_data_transfer_has_no_http_imports() : void
    {
        $output = [];
        exec('grep -R "^use.*Components..*HTTP" ' . escapeshellarg(__DIR__ . '/../../../../components/DataStack/DataTransfer/System') . ' -n --include="*.php" 2>/dev/null', $output);
        $this->assertEmpty($output, 'DataTransfer core must not import HTTP');
    }

    public function test_data_transfer_has_no_secure_request_imports() : void
    {
        $output = [];
        exec('grep -R "^use.*SecureRequest" ' . escapeshellarg(__DIR__ . '/../../../../components/DataStack/DataTransfer/System') . ' -n --include="*.php" 2>/dev/null', $output);
        $this->assertEmpty($output, 'DataTransfer core must not import SecureRequest');
    }

    public function test_no_gemini_namespaces() : void
    {
        $output = [];
        exec('grep -R "^use.*Gemini\\\\\\|namespace Gemini" ' . escapeshellarg(__DIR__ . '/../../../../components') . ' ' . escapeshellarg(__DIR__ . '/../../../../framework') . ' ' . escapeshellarg(__DIR__ . '/../../../../tests') . ' -n --include="*.php" 2>/dev/null', $output);
        $this->assertEmpty($output, 'No Gemini namespaces must remain');
    }

    // -- Helpers --

    protected function setUp() : void
    {
        // Minimal container that resolves nothing
        $container = new class implements ContainerInterface {
            public function has(string $id) : bool { return false; }

            public function get(string $id) : mixed { return null; }
        };

        $this->resolver         = new ArgumentResolver($container);
        $this->exceptionHandler = new CatchUnhandledExceptions(
            new ReportExceptionToLogger(),
            new CreateJsonResponse(),
        );
    }
}

final class TestIntegrationRegisterRequest extends SecureRequest
{
    #[Required]
    #[StringType]
    #[Email]
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

final class TestIntegrationAdminRequest extends SecureRequest
{
    #[Required]
    #[StringType]
    #[Email]
    public string $email;

    public function authorize() : bool
    {
        return false;
    }
}

final class TestIntegrationFileRequest extends SecureRequest
{
    #[Required]
    #[StringType]
    public string $title;

    public mixed $document = null;

    public function authorize() : bool
    {
        return true;
    }
}
