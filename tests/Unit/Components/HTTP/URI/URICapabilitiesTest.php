<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\URI;

use Avax\Components\HTTP\URI\System\System\Capabilities\Parts\Scheme;
use PHPUnit\Framework\TestCase;

final class URICapabilitiesTest extends TestCase
{
    public function test_scheme_normalization() : void
    {
        $scheme = new Scheme('HTTP');
        $this->assertSame('http', (string) $scheme);
    }
}
