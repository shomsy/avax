<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\Http;

use Avax\Auth\Integrations\Http\HttpAuthenticationInput;
use Avax\Auth\Integrations\Http\MapAuthenticationRequest;
use PHPUnit\Framework\TestCase;

final class MapAuthenticationRequestTest extends TestCase
{
    public function testMapAuthenticationRequestReadsBearerTokenAndClientMetadata() : void
    {
        $request = (new MapAuthenticationRequest())->execute(new HttpAuthenticationInput(
                                                                 headers          : ['authorization' => 'Bearer token-123'],
                                                                 cookies          : ['PHPSESSID' => 'session-1'],
                                                                 server           : [
                                                                                        'REMOTE_ADDR'     => '127.0.0.1',
                                                                                        'HTTP_USER_AGENT' => 'phpunit'
                                                                                    ],
                                                                 sessionCookieName: 'PHPSESSID'
                                                             ));

        $this->assertSame('token-123', $request->bearerToken);
        $this->assertTrue($request->allowSession);
        $this->assertSame('127.0.0.1', $request->ipAddress);
        $this->assertSame('phpunit', $request->userAgent);
    }

    public function testMapAuthenticationRequestDisablesSessionWhenCookieIsMissing() : void
    {
        $request = (new MapAuthenticationRequest())->execute(new HttpAuthenticationInput(
                                                                 headers          : ['Authorization' => 'Basic abc'],
                                                                 cookies          : [],
                                                                 server           : ['HTTP_AUTHORIZATION' => 'Bearer server-token'],
                                                                 sessionCookieName: 'PHPSESSID'
                                                             ));

        $this->assertSame('server-token', $request->bearerToken);
        $this->assertFalse($request->allowSession);
    }

    public function testMapAuthenticationRequestKeepsSessionDisabledWhenTransportRejectsIt() : void
    {
        $request = (new MapAuthenticationRequest())->execute(new HttpAuthenticationInput(
                                                                 headers          : ['User-Agent' => 'header-agent'],
                                                                 cookies          : ['PHPSESSID' => 'session-1'],
                                                                 allowSession     : false,
                                                                 sessionCookieName: 'PHPSESSID'
                                                             ));

        $this->assertNull($request->bearerToken);
        $this->assertFalse($request->allowSession);
        $this->assertSame('header-agent', $request->userAgent);
    }
}
