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
        $attributes = new RequestAttributes(['explicit_null' => null, 'value' => 123]);

        $this->assertTrue($attributes->has('explicit_null'), 'Attributes must recognize null as present');
        $this->assertTrue($attributes->has('value'));
        $this->assertFalse($attributes->has('missing'));

        $this->assertNull($attributes->get('explicit_null', 'default'));
        $this->assertEquals('default', $attributes->get('missing', 'default'));
    }

    public function test_cookies_null_semantics()
    {
        $cookies = new RequestCookies(['explicit_null' => null, 'value' => 'abc']);

        $this->assertTrue($cookies->has('explicit_null'), 'Cookies must recognize null as present');
        $this->assertTrue($cookies->has('value'));
        $this->assertFalse($cookies->has('missing'));

        $this->assertNull($cookies->get('explicit_null', 'default'));
        $this->assertEquals('default', $cookies->get('missing', 'default'));
    }

    public function test_session_null_semantics()
    {
        $session = new RequestSession(['explicit_null' => null, 'value' => 'abc'], 'sid');

        $this->assertTrue($session->has('explicit_null'), 'Session must recognize null as present');
        $this->assertTrue($session->has('value'));
        $this->assertFalse($session->has('missing'));

        $this->assertNull($session->get('explicit_null', 'default'));
        $this->assertEquals('default', $session->get('missing', 'default'));
    }
}
