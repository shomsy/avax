<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\Http;

use Avax\Auth\Examples\Http\RequireCsrfProtection;
use PHPUnit\Framework\TestCase;

final class RequireCsrfProtectionTest extends TestCase
{
    public function testSafeMethodsBypassCsrfValidation() : void
    {
        $middleware = new RequireCsrfProtection();

        $this->assertTrue(condition: $middleware->execute(method: 'GET'));
        $this->assertTrue(condition: $middleware->execute(method: 'HEAD'));
    }

    public function testUnsafeMethodRequiresSameOriginAndMatchingToken() : void
    {
        $middleware = new RequireCsrfProtection();

        $this->assertTrue(condition: $middleware->execute(
            method  : 'POST',
            server  : [
                'HTTP_ORIGIN' => 'https://app.example.test',
                'HTTP_HOST' => 'app.example.test',
            ],
            headers : [
                'X-CSRF-TOKEN' => 'csrf-1',
            ],
            cookies : [
                'csrf_token' => 'csrf-1',
            ]
        ));
    }

    public function testUnsafeMethodRejectsCrossOriginRequest() : void
    {
        $middleware = new RequireCsrfProtection();

        $this->assertFalse(condition: $middleware->execute(
            method  : 'POST',
            server  : [
                'HTTP_ORIGIN' => 'https://evil.example.test',
                'HTTP_HOST' => 'app.example.test',
            ],
            headers : [
                'X-CSRF-TOKEN' => 'csrf-1',
            ],
            cookies : [
                'csrf_token' => 'csrf-1',
            ]
        ));
    }
}
