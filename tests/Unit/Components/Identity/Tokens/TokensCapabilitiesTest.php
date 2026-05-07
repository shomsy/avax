<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Tokens;

use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Tokens\AccessToken;
use PHPUnit\Framework\TestCase;

final class TokensCapabilitiesTest extends TestCase
{
    public function test_access_token_identification() : void
    {
        $token = new AccessToken('user-1', ['read'], time() + 3600, time(), 'token-1');
        $this->assertSame('user-1', $token->sub);
        $this->assertSame(['read'], $token->scopes);
    }
}
