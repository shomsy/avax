<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Security;

use Avax\Components\HTTP\Security\System\System\Capabilities\Csrf\CsrfToken;
use Avax\Components\HTTP\Security\System\System\Capabilities\Csrf\CsrfVerifier;
use PHPUnit\Framework\TestCase;

final class HttpSecurityCapabilitiesTest extends TestCase
{
    public function test_csrf_verifier_matches_tokens() : void
    {
        $token = 'valid-token';

        $this->assertTrue(CsrfVerifier::verify($token, 'valid-token'));
        $this->assertFalse(CsrfVerifier::verify($token, 'invalid-token'));
    }
}
