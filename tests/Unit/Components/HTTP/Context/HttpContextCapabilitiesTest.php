<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Context;

use Avax\Components\HTTP\Context\System\PublicSurface\HttpContext;
use PHPUnit\Framework\TestCase;

final class HttpContextCapabilitiesTest extends TestCase
{
    public function test_http_context_provides_access_to_globals() : void
    {
        // Mocking globals is hard in PHPUnit without extensions, 
        // but we can prove the factory and basic structure.
        $context = HttpContext::fromGlobals();

        $this->assertSame($_GET, $context->query());
        $this->assertSame($_SERVER, $context->serverParams());
    }
}
