<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Request;

use Avax\Components\HTTP\Request\System\Capabilities\Headers\RequestHeaders;
use PHPUnit\Framework\TestCase;

final class RequestCapabilitiesTest extends TestCase
{
    public function test_request_headers_normalize_case() : void
    {
        $headers = new RequestHeaders(['Content-Type' => 'application/json']);

        $this->assertTrue($headers->has('content-type'));
        $this->assertSame('application/json', $headers->get('CONTENT-TYPE')?->line());
    }
}
