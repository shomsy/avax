<?php

declare(strict_types=1);

use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Response\Classes\Response;
use Avax\HTTP\Response\Classes\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Unit tests for ControllerDispatcher
 */
class ControllerDispatcherUnitTest extends TestCase
{
    private ControllerDispatcher $dispatcher;
    private ContainerInterface $container;

    /**
     * @test
     */
    public function dispatch_callable_returns_response_when_callable_returns_null() : void
    {
        // Given: A callable that returns null
        $callable = fn(Request $request) => null;

        // Mock request
        $request = $this->createMock(Request::class);

        // When: Dispatching the callable
        $response = $this->dispatcher->dispatch($callable, $request);

        // Then: Returns a Response with "Callable returned null" message
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('Callable returned null. Must return a ResponseInterface.', (string) $response->getBody());
    }

    /**
     * @test
     */
    public function dispatch_controller_method_returns_response_when_method_returns_null() : void
    {
        // Given: A controller with a method that returns null
        $controller = new class {
            public function testMethod(Request $request): ?ResponseInterface {
                return null;
            }
        };

        // Mock container to return the controller instance
        $this->container->method('has')->willReturn(true);
        $this->container->method('get')->willReturn($controller);

        // Mock request
        $request = $this->createMock(Request::class);

        // When: Dispatching the controller method
        $response = $this->dispatcher->dispatch([get_class($controller), 'testMethod'], $request);

        // Then: Returns a Response with "Controller returned null" message
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('Controller returned null', (string) $response->getBody());
    }

    /**
     * @test
     */
    public function dispatch_invokable_controller_returns_response_when_controller_returns_null() : void
    {
        // Given: An invokable controller that returns null
        $controller = new class {
            public function __invoke(Request $request): ?ResponseInterface {
                return null;
            }
        };

        // Mock request
        $request = $this->createMock(Request::class);

        // When: Dispatching the invokable controller
        $response = $this->dispatcher->dispatch($controller::class, $request);

        // Then: Returns a Response with "Controller returned null" message
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('Controller returned null', (string) $response->getBody());
    }

    protected function setUp() : void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->dispatcher = new ControllerDispatcher($this->container);
    }
}