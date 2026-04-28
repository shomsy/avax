<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Integrations\Http;

use Avax\Components\Identity\Auth\Integrations\Http\HttpAuthenticationInput;
use Avax\Components\Identity\Auth\Integrations\Http\MapAuthenticationRequest;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class MapAuthenticationRequestTest extends TestCase
{
    public function testMapAuthenticationRequestReadsBearerTokenAndClientMetadata() : void
    {
        $request = new MapAuthenticationRequest()->execute(input: new HttpAuthenticationInput(
                                                                      headers          : ['authorization' => 'Bearer token-123'],
                                                                      cookies          : ['PHPSESSID' => 'session-1'],
                                                                      server           : [
                                                                                             'REMOTE_ADDR'     => '127.0.0.1',
                                                                                             'HTTP_USER_AGENT' => 'phpunit'
                                                                                         ],
                                                                      sessionCookieName: 'PHPSESSID'
                                                                  ));

        $this->assertSame(expected: 'token-123', actual: $request->bearerToken);
        $this->assertTrue(condition: $request->allowSession);
        $this->assertSame(expected: '127.0.0.1', actual: $request->ipAddress);
        $this->assertSame(expected: 'phpunit', actual: $request->userAgent);
    }

    public function testMapAuthenticationRequestDisablesSessionWhenCookieIsMissing() : void
    {
        $request = new MapAuthenticationRequest()->execute(input: new HttpAuthenticationInput(
                                                                      headers          : ['Authorization' => 'Basic abc'],
                                                                      cookies          : [],
                                                                      server           : ['HTTP_AUTHORIZATION' => 'Bearer server-token'],
                                                                      sessionCookieName: 'PHPSESSID'
                                                                  ));

        $this->assertSame(expected: 'server-token', actual: $request->bearerToken);
        $this->assertFalse(condition: $request->allowSession);
    }

    public function testMapAuthenticationRequestKeepsSessionDisabledWhenTransportRejectsIt() : void
    {
        $request = new MapAuthenticationRequest()->execute(input: new HttpAuthenticationInput(
                                                                      headers          : ['User-Agent' => 'header-agent'],
                                                                      cookies          : ['PHPSESSID' => 'session-1'],
                                                                      allowSession     : false,
                                                                      sessionCookieName: 'PHPSESSID'
                                                                  ));

        $this->assertNull(actual: $request->bearerToken);
        $this->assertFalse(condition: $request->allowSession);
        $this->assertSame(expected: 'header-agent', actual: $request->userAgent);
    }
}
