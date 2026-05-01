<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Tokens;

use Avax\Components\Identity\Tokens\System\PublicSurface\Tokens;
use Avax\Tests\TestCase;
use RuntimeException;

final class TokensPublicSurfaceTest extends TestCase
{
    public function test_authorization_code_can_be_exchanged_introspected_and_revoked() : void
    {
        $tokens = Tokens::hmac(secret: str_repeat(string: 'a', times: 64));

        $authorization = $tokens->authorize(request: [
                                                         'subject'   => 'user-123',
                                                         'client_id' => 'dashboard',
                                                         'scope'     => 'profile.read profile.write',
                                                     ]);
        $tokenPair     = $tokens->exchangeCode(code: $authorization->code);

        $introspection = $tokens->introspect(token: $tokenPair->access_token);

        self::assertTrue(condition: $introspection->active);
        self::assertSame(expected: 'user-123', actual: $introspection->sub);
        self::assertSame(expected: 'profile.read profile.write', actual: $introspection->scope);

        $tokens->revoke(token: $tokenPair->access_token);

        self::assertFalse(condition: $tokens->introspect(token: $tokenPair->access_token)->active);
    }

    public function test_authorization_code_is_single_use() : void
    {
        $tokens        = Tokens::hmac(secret: str_repeat(string: 'b', times: 64));
        $authorization = $tokens->authorize(request: ['subject' => 'user-123']);

        $tokens->exchangeCode(code: $authorization->code);

        $this->expectException(RuntimeException::class);

        $tokens->exchangeCode(code: $authorization->code);
    }
}
