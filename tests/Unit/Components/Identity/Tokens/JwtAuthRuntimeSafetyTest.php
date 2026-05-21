<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Tokens;

use Avax\Components\Identity\Tokens\System\Configuration\Assembly\JwtAuthGraph;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class JwtAuthRuntimeSafetyTest extends TestCase
{
    #[Test]
    public function issuedAccessTokenCanBeVerified(): void
    {
        $jwtAuth = JwtAuthGraph::hmac(secret: 'test-secret');

        $tokenPair = $jwtAuth->issue(user: ['id' => 'user-1'], scopes: ['read']);

        $accessToken = $jwtAuth->verify(token: $tokenPair->accessToken);

        self::assertSame('user-1', $accessToken->sub);
        self::assertSame(['read'], $accessToken->scopes);
    }

    #[Test]
    public function revokedTokenFailsClosedForSameRuntime(): void
    {
        $jwtAuth = JwtAuthGraph::hmac(secret: 'test-secret');
        $tokenPair = $jwtAuth->issue(user: ['id' => 'user-1']);

        $jwtAuth->revoke(token: $tokenPair->accessToken);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Token has been revoked');

        $jwtAuth->verify(token: $tokenPair->accessToken);
    }

    #[Test]
    public function separateJwtAuthRuntimesDoNotShareRevocationState(): void
    {
        $first = JwtAuthGraph::hmac(secret: 'test-secret');
        $second = JwtAuthGraph::hmac(secret: 'test-secret');
        $tokenPair = $first->issue(user: ['id' => 'user-1'], scopes: ['read']);

        $first->revoke(token: $tokenPair->accessToken);

        self::assertFalse($first->introspect(token: $tokenPair->accessToken)['active']);
        self::assertTrue($second->introspect(token: $tokenPair->accessToken)['active']);
    }
}
