<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Response;

use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Components\HTTP\Response\System\Configuration\ResponseServiceProvider;
use Avax\Components\HTTP\Response\System\Flows\BuildResponse\BuildResponse;
use Avax\Components\HTTP\Response\System\PublicSurface\Responses;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;

final class ResponseServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private ResponseServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider  = new ResponseServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_create_http_response_resolves(): void
    {
        $capability = $this->container->get(CreateHttpResponse::class);

        $this->assertInstanceOf(CreateHttpResponse::class, $capability);
    }

    public function test_responses_resolves(): void
    {
        $responses = $this->container->get(Responses::class);

        $this->assertInstanceOf(Responses::class, $responses);
    }

    public function test_response_factory_interface_resolves_to_responses(): void
    {
        $factory = $this->container->get(ResponseFactoryInterface::class);
        $responses = $this->container->get(Responses::class);

        // Both resolve to Responses instances via alias binding
        $this->assertInstanceOf(Responses::class, $factory);
        $this->assertInstanceOf(Responses::class, $responses);
    }

    public function test_responses_delegates_to_create_http_response(): void
    {
        /** @var Responses $responses */
        $responses = $this->container->get(Responses::class);

        $response = $responses->json(['key' => 'value'], 201);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaderLine('content-type'));
    }

    public function test_responses_creates_psr17_response(): void
    {
        /** @var ResponseFactoryInterface $factory */
        $factory = $this->container->get(ResponseFactoryInterface::class);

        $response = $factory->createResponse(204);

        $this->assertSame(204, $response->getStatusCode());
    }

    public function test_no_runtime_new_response_factory_remains(): void
    {
        // Verify that ResponseFactoryInterface resolves to Responses, not a legacy factory
        $factory = $this->container->get(ResponseFactoryInterface::class);

        $this->assertInstanceOf(Responses::class, $factory);
    }

    public function test_build_response_flow_still_resolves(): void
    {
        $buildResponse = $this->container->get(BuildResponse::class);

        $this->assertInstanceOf(BuildResponse::class, $buildResponse);
    }

    public function test_responses_json_returns_response_interface(): void
    {
        /** @var Responses $responses */
        $responses = $this->container->get(Responses::class);

        $response = $responses->json(['status' => 'ok']);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaderLine('content-type'));
    }

    public function test_responses_html_returns_response_interface(): void
    {
        /** @var Responses $responses */
        $responses = $this->container->get(Responses::class);

        $response = $responses->html('<h1>Hello</h1>');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('text/html', $response->getHeaderLine('content-type'));
    }

    public function test_responses_text_returns_response_interface(): void
    {
        /** @var Responses $responses */
        $responses = $this->container->get(Responses::class);

        $response = $responses->text('Hello World');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('text/plain', $response->getHeaderLine('content-type'));
    }

    public function test_responses_redirect_returns_response_interface(): void
    {
        /** @var Responses $responses */
        $responses = $this->container->get(Responses::class);

        $response = $responses->redirect('/home', 301);

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('/home', $response->getHeaderLine('location'));
    }

    public function test_responses_empty_returns_response_interface(): void
    {
        /** @var Responses $responses */
        $responses = $this->container->get(Responses::class);

        $response = $responses->empty(204);

        $this->assertSame(204, $response->getStatusCode());
    }

    public function test_responses_error_returns_json_error_response(): void
    {
        /** @var Responses $responses */
        $responses = $this->container->get(Responses::class);

        $response = $responses->error('Something went wrong', 503);

        $this->assertSame(503, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaderLine('content-type'));
    }
}
