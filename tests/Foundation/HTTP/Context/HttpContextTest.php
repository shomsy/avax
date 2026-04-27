<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Context;

use Avax\HTTP\Context\GlobalsProviderInterface;
use Avax\HTTP\Context\HttpContext;
use Avax\Tests\TestCase;
use Nyholm\Psr7\ServerRequest;

final class HttpContextTest extends TestCase
{
    public function test_prefers_request_values_over_globals() : void
    {
        $request = new ServerRequest(
            'GET',
            'https://example.com:8443/demo?x=1',
            [
                'User-Agent'    => 'TestAgent/1.0',
                'Authorization' => 'Bearer token',
            ],
            null,
            '1.1',
            [
                'REMOTE_ADDR' => '203.0.113.10',
            ],
        )->withCookieParams(['theme' => 'dark']);

        $context = new HttpContext(
            request: $request,
            globals: $this->globals(),
        );

        self::assertSame('https://example.com:8443', $context->baseUrl());
        self::assertSame('203.0.113.10', $context->clientIp());
        self::assertSame('TestAgent/1.0', $context->userAgent());
        self::assertSame('Bearer token', $context->authHeader());
        self::assertSame(['theme' => 'dark'], $context->cookies());
    }

    private function globals(array|null $server = null, array $cookies = []) : GlobalsProviderInterface
    {
        $server ??= [];

        return new class($server, $cookies) implements GlobalsProviderInterface {
            public function __construct(
                private array $server,
                private array $cookies,
            ) {}

            public function server() : array
            {
                return $this->server;
            }

            public function get() : array
            {
                return [];
            }

            public function post() : array
            {
                return [];
            }

            public function files() : array
            {
                return [];
            }

            public function cookies() : array
            {
                return $this->cookies;
            }
        };
    }

    public function test_falls_back_to_globals_when_request_is_missing() : void
    {
        $context = new HttpContext(
            request: null,
            globals: $this->globals(
                         server : [
                                      'HTTPS'           => 'on',
                                      'HTTP_HOST'       => 'fallback.local',
                                      'REMOTE_ADDR'     => '198.51.100.20',
                                      'HTTP_USER_AGENT' => 'GlobalAgent/1.0',
                                  ],
                         cookies: ['language' => 'sr'],
                     ),
        );

        self::assertSame('https://fallback.local', $context->baseUrl());
        self::assertSame('198.51.100.20', $context->clientIp());
        self::assertSame('GlobalAgent/1.0', $context->userAgent());
        self::assertSame(['language' => 'sr'], $context->cookies());
    }
}
