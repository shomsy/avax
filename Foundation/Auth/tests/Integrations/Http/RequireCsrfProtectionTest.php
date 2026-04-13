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

        $this->assertTrue($middleware->execute('GET'));
        $this->assertTrue($middleware->execute('HEAD'));
    }

    public function testUnsafeMethodRequiresSameOriginAndMatchingToken() : void
    {
        $middleware = new RequireCsrfProtection();

        $this->assertTrue($middleware->execute(
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

        $this->assertFalse($middleware->execute(
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
