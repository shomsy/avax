<?php

declare(strict_types=1);

use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Request\RequestDtoFactory;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\AssembleIncomingRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\Configuration\PrepareRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ProtocolVersion\NormalizeProtocolVersion;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseBodyByContentType;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseFormBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseJsonBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Mapping\MapRequestedInputsToDto;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization\InputSanitizer;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\NormalizeUploadedFiles;
use Avax\HTTP\Request\ServerRequest\Network\ParseForwardedAddresses;
use Avax\HTTP\Request\ServerRequest\Network\ResolveClientAddress;
use Avax\HTTP\Request\ServerRequest\Network\TrustedIpv4ProxyPolicy;
use Avax\HTTP\Response\Response;
use Avax\HTTP\URI\UriBuilder;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Unit tests for ControllerDispatcher
 */
class ControllerDispatcherUnitTest extends TestCase
{
    private ControllerDispatcher $dispatcher;
    private ContainerInterface   $container;

    /**
     * @test
     */
    public function dispatch_callable_returns_response_when_callable_returns_null() : void
    {
        // Given: A callable that returns null
        $callable = static fn (Request $request) => null;

        // Mock request
        $request = $this->createMock(Request::class);

        // When: Dispatching the callable
        $response = $this->dispatcher->dispatch(action: $callable, request: $request);

        // Then: Returns a Response with "Callable returned null" message
        $this->assertInstanceOf(expected: ResponseInterface::class, actual: $response);
        $this->assertEquals(expected: 'Callable returned null. Must return a ResponseInterface.', actual: (string) $response->getBody());
    }

    /**
     * @test
     */
    public function dispatch_controller_method_returns_response_when_method_returns_null() : void
    {
        // Given: A controller with a method that returns null
        $controller = new class {
            public function testMethod(Request $request) : ResponseInterface|null
            {
                return null;
            }
        };

        // Mock container to return the controller instance
        $this->container->method('has')->willReturn(value: true);
        $this->container->method('get')->willReturn(value: $controller);

        // Mock request
        $request = $this->createMock(Request::class);

        // When: Dispatching the controller method
        $response = $this->dispatcher->dispatch(action: [get_class(object: $controller), 'testMethod'], request: $request);

        // Then: Returns a Response with "Controller returned null" message
        $this->assertInstanceOf(expected: ResponseInterface::class, actual: $response);
        $this->assertEquals(expected: 'Controller returned null', actual: (string) $response->getBody());
    }

    /**
     * @test
     */
    public function dispatch_invokable_controller_returns_response_when_controller_returns_null() : void
    {
        // Given: An invokable controller that returns null
        $controller = new class {
            public function __invoke(Request $request) : ResponseInterface|null
            {
                return null;
            }
        };

        // Mock request
        $request = $this->createMock(Request::class);

        // When: Dispatching the invokable controller
        $response = $this->dispatcher->dispatch(action: $controller::class, request: $request);

        // Then: Returns a Response with "Controller returned null" message
        $this->assertInstanceOf(expected: ResponseInterface::class, actual: $response);
        $this->assertEquals(expected: 'Controller returned null', actual: (string) $response->getBody());
    }

    /**
     * @test
     */
    public function dispatch_controller_method_autowires_request_dto_from_server_request() : void
    {
        // Given: A controller that expects a Request DTO parameter
        $controller = new class {
            public function handle(Request $request) : ResponseInterface
            {
                // Verify that the Request DTO has access to ServerRequest
                $this->assertEquals('POST', $request->method());
                $this->assertEquals('example.com', $request->uri()->getHost());

                return Response::text(content: 'success');
            }
        };

        // Create a real ServerRequest using the assembler
        $assembler = new AssembleIncomingRequest(
            preparer : new PrepareRequest(
                           bodyParser        : new ParseBodyByContentType(
                                                   jsonParser: new ParseJsonBody(),
                                                   formParser: new ParseFormBody()
                                               ),
                           protocolNormalizer: new NormalizeProtocolVersion(),
                           filesNormalizer   : new NormalizeUploadedFiles(),
                           trustedProxyPolicy: new TrustedIpv4ProxyPolicy(),
                           clientResolver    : new ResolveClientAddress(
                                                   proxyPolicy    : new TrustedIpv4ProxyPolicy(),
                                                   forwardedParser: new ParseForwardedAddresses()
                                               )
                       ),
            sanitizer: new InputSanitizer(),
            mapper   : new MapRequestedInputsToDto()
        );

        $serverRequest = $assembler->fromSlices(
            queryParams: ['q' => 'test'],
            parsedBody : ['data' => 'value'],
            method     : 'POST'
        )->withUri(uri: UriBuilder::createFromString(uri: 'https://example.com/api'));

        // Mock container to return RequestDtoFactory
        $factory = new RequestDtoFactory();
        $this->container->method('has')->willReturnCallback(
            static fn ($id) => $id === RequestDtoFactory::class || $id === get_class(object: $controller)
        );
        $this->container->method('get')->willReturnCallback(
            static fn ($id) => match ($id) {
                RequestDtoFactory::class       => $factory,
                get_class(object: $controller) => $controller,
                default                        => null
            }
        );

        // When: Dispatching the controller method with ServerRequest
        $response = $this->dispatcher->dispatch(
            action : [get_class(object: $controller), 'handle'],
            request: $serverRequest
        );

        // Then: Returns success response (DTO was properly autowired)
        $this->assertInstanceOf(expected: ResponseInterface::class, actual: $response);
        $this->assertEquals(expected: 'success', actual: (string) $response->getBody());
    }

    #[Override]
    protected function setUp() : void
    {
        $this->container  = $this->createMock(ContainerInterface::class);
        $this->dispatcher = new ControllerDispatcher(container: $this->container);
    }
}
