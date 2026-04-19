<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestAttributes\RequestAttributes;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestCookies\RequestCookies;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestSession\RequestSession;
use PHPUnit\Framework\TestCase;

class CapabilitySemanticsTest extends TestCase
{
    public function test_attributes_null_semantics()
    {
        $attributes = new RequestAttributes(attributes: ['explicit_null' => null, 'value' => 123]);

        $this->assertTrue(condition: $attributes->has(name: 'explicit_null'), message: 'Attributes must recognize null as present');
        $this->assertTrue(condition: $attributes->has(name: 'value'));
        $this->assertFalse(condition: $attributes->has(name: 'missing'));

        $this->assertNull(actual: $attributes->get(name: 'explicit_null', default: 'default'));
        $this->assertEquals(expected: 'default', actual: $attributes->get(name: 'missing', default: 'default'));
    }

    public function test_cookies_null_semantics()
    {
        $cookies = new RequestCookies(cookies: ['explicit_null' => null, 'value' => 'abc']);

        $this->assertTrue(condition: $cookies->has(name: 'explicit_null'), message: 'Cookies must recognize null as present');
        $this->assertTrue(condition: $cookies->has(name: 'value'));
        $this->assertFalse(condition: $cookies->has(name: 'missing'));

        $this->assertNull(actual: $cookies->get(name: 'explicit_null', default: 'default'));
        $this->assertEquals(expected: 'default', actual: $cookies->get(name: 'missing', default: 'default'));
    }

    public function test_session_null_semantics()
    {
        $session = new RequestSession(data: ['explicit_null' => null, 'value' => 'abc'], id: 'sid');

        $this->assertTrue(condition: $session->has(key: 'explicit_null'), message: 'Session must recognize null as present');
        $this->assertTrue(condition: $session->has(key: 'value'));
        $this->assertFalse(condition: $session->has(key: 'missing'));

        $this->assertNull(actual: $session->get(key: 'explicit_null', default: 'default'));
        $this->assertEquals(expected: 'default', actual: $session->get(key: 'missing', default: 'default'));
    }
}
