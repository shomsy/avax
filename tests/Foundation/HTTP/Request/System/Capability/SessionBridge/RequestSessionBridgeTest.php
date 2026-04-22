<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Request\System\Capability\SessionBridge;

use Avax\HTTP\Request\System\Capability\SessionBridge\RequestSessionBridge;
use Avax\HTTP\Session\Shared\Contracts\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RequestSessionBridgeTest extends TestCase
{
    private RequestSessionBridge        $bridge;
    private MockObject&SessionInterface $session;

    public function test_session_returns_session_instance() : void
    {
        $result = $this->bridge->session();

        $this->assertSame(expected: $this->session, actual: $result);
    }

    public function test_session_with_key_returns_value() : void
    {
        $this->session->method('has')->willReturn(value: true);
        $this->session->method('get')->willReturn(value: 'user-value');

        $result = $this->bridge->session('user');

        $this->assertSame(expected: 'user-value', actual: $result);
    }

    public function test_session_with_key_returns_default_when_missing() : void
    {
        $this->session->method('has')->willReturn(value: false);

        $result = $this->bridge->session('missing', 'default');

        $this->assertSame(expected: 'default', actual: $result);
    }

    public function test_set_session_attaches_session() : void
    {
        $newSession = $this->createMock(SessionInterface::class);

        $this->bridge->setSession($newSession);

        $this->assertSame(expected: $newSession, actual: $this->bridge->session());
    }

    public function test_has_session_returns_true_when_key_exists() : void
    {
        $this->session->method('has')->willReturn(value: true);

        $result = $this->bridge->hasSession('key');

        $this->assertTrue(condition: $result);
    }

    public function test_has_session_returns_false_when_key_missing() : void
    {
        $this->session->method('has')->willReturn(value: false);

        $result = $this->bridge->hasSession('key');

        $this->assertFalse(condition: $result);
    }

    public function test_put_session_stores_value() : void
    {
        $this->session->expects(invocationRule: $this->once())
            ->method(constraint: 'set')
            ->with('key', 'value');

        $this->bridge->putSession('key', 'value');
    }

    public function test_forget_session_removes_value() : void
    {
        $this->session->expects(invocationRule: $this->once())
            ->method(constraint: 'remove')
            ->with('key');

        $this->bridge->forgetSession('key');
    }

    public function test_user_returns_user_from_session() : void
    {
        $user = ['id' => 1, 'name' => 'John'];
        $this->session->method('get')->willReturn(value: $user);

        $result = $this->bridge->user();

        $this->assertSame(expected: $user, actual: $result);
    }

    public function test_user_returns_null_when_not_set() : void
    {
        $this->session->method('get')->willReturn(value: null);

        $result = $this->bridge->user();

        $this->assertNull(actual: $result);
    }

    protected function setUp() : void
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->bridge  = new RequestSessionBridge($this->session);
    }
}
