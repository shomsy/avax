<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\ContentNegotiation;

use Avax\Components\HTTP\ContentNegotiation\System\System\Capabilities\Negotiator\AcceptHeaderParser;
use PHPUnit\Framework\TestCase;

final class ContentNegotiationCapabilitiesTest extends TestCase
{
    public function test_accept_header_parser_parses_priorities() : void
    {
        $header = 'application/json;q=0.9, text/html, application/xml;q=0.5';

        $priorities = AcceptHeaderParser::parse($header);

        $this->assertSame('text/html', $priorities[0]);
        $this->assertSame('application/json', $priorities[1]);
        $this->assertSame('application/xml', $priorities[2]);
    }
}
