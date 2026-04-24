<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Response;

use Avax\HTTP\Response\ResponseFactory;
use Avax\Tests\TestCase;
use InvalidArgumentException;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class ResponseFactoryTest extends TestCase
{
    public function test_creates_html_response_with_expected_content_type() : void
    {
        $factory  = $this->factory();
        $response = $factory->createHtmlResponse('<h1>Hello</h1>', 201);

        self::assertSame(201, $response->getStatusCode());
        self::assertSame('text/html; charset=UTF-8', $response->getHeaderLine('Content-Type'));
        self::assertSame('<h1>Hello</h1>', (string) $response->getBody());
    }

    private function factory() : ResponseFactory
    {
        $psr17 = new Psr17Factory();

        return new ResponseFactory(
            streamFactory: $psr17,
            response     : $psr17->createResponse(),
        );
    }

    public function test_creates_error_response_as_json() : void
    {
        $factory  = $this->factory();
        $response = $factory->createErrorResponse(429, 'Too Many Requests');

        self::assertSame(429, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        self::assertStringContainsString('Too Many Requests', (string) $response->getBody());
    }

    public function test_response_dispatches_strings_arrays_and_existing_responses() : void
    {
        $factory = $this->factory();

        $textResponse = $factory->response(data: 'hello');
        $jsonResponse = $factory->response(data: ['name' => 'Alice']);
        $sameResponse = $factory->response(data: $textResponse);

        self::assertSame('text/plain; charset=UTF-8', $textResponse->getHeaderLine('Content-Type'));
        self::assertSame('hello', (string) $textResponse->getBody());
        self::assertSame('application/json', $jsonResponse->getHeaderLine('Content-Type'));
        self::assertSame(['name' => 'Alice'], json_decode(json: (string) $jsonResponse->getBody(), associative: true));
        self::assertSame($textResponse, $sameResponse);
    }

    public function test_create_response_with_body_applies_headers() : void
    {
        $response = $this->factory()->createResponseWithBody(
            content: 'payload',
            status : 202,
            headers: ['X-Test' => 'present'],
        );

        self::assertSame(202, $response->getStatusCode());
        self::assertSame('present', $response->getHeaderLine('X-Test'));
        self::assertSame('payload', (string) $response->getBody());
    }

    public function test_create_redirect_response_rejects_invalid_targets() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->factory()->createRedirectResponse('invalid target');
    }
}
