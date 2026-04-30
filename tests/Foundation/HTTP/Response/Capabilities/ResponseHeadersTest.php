<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Response\Capabilities;

use Avax\Components\HTTP\Response\Capabilities\Cookies\ResponseCookie;
use Avax\Components\HTTP\Response\Capabilities\Cookies\SetCookieHeader;
use Avax\Components\HTTP\Response\Capabilities\Headers\ResponseHeaders;
use Avax\Components\HTTP\Response\Response;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ResponseHeadersTest extends TestCase
{
    public function test_headers_are_case_insensitive_and_immutable() : void
    {
        $headers = new ResponseHeaders(headers: ['Content-Type' => 'text/plain']);
        $changed = $headers
            ->append(name: 'X-Test', value: 'one')
            ->append(name: 'x-test', value: 'two');

        self::assertTrue($headers->has(name: 'content-type'));
        self::assertFalse($headers->has(name: 'x-test'));
        self::assertSame(['one', 'two'], $changed->read(name: 'X-Test'));
        self::assertSame('one, two', $changed->readLine(name: 'x-test'));
    }

    public function test_invalid_header_name_and_values_are_rejected() : void
    {
        $this->expectException(InvalidArgumentException::class);

        new ResponseHeaders()->replace(name: "Bad Header", value: "test\r\ninjection");
    }

    public function test_set_cookie_header_appends_cookie_lines() : void
    {
        $response = new SetCookieHeader()(
            response: Response::empty(),
            cookie  : new ResponseCookie(name: 'session', value: 'abc'),
        );

        self::assertStringContainsString('session=abc', $response->getHeaderLine(name: 'Set-Cookie'));
    }
}
