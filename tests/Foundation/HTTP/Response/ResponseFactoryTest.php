<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Response;

use Avax\HTTP\Response\ResponseFactory;
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
}
