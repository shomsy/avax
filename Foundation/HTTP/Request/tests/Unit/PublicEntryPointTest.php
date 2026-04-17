<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

use Avax\HTTP\Request\IncomingHttp\IncomingRequest\PublicEntryPointRequest;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\ServerRequest;
use PHPUnit\Framework\TestCase;

class PublicEntryPointTest extends TestCase
{
    public function test_public_entry_point_creates_server_request_from_current_http_environment()
    {
        // Mocking superglobals isn't easy in PHPUnit, but we can check if it returns a ServerRequest
        // This will likely fail with a fatal error due to the argument mismatch if not fixed.
        $request = PublicEntryPointRequest::fromIncomingHttp();

        $this->assertInstanceOf(expected: ServerRequest::class, actual: $request);
    }
}
