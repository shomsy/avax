<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Response;

use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use PHPUnit\Framework\TestCase;

final class ResponseCapabilitiesTest extends TestCase
{
    public function test_response_manages_status_and_headers() : void
    {
        $response   = Response::text('Created', 201);
        $withHeader = $response->withHeader('X-Test', 'foo');

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('Created', (string) $response->getBody());
        $this->assertFalse($response->hasHeader('X-Test'));
        $this->assertSame('foo', $withHeader->getHeaderLine('X-Test'));
    }
}
