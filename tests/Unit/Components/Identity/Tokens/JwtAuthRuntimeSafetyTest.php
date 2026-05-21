<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Tokens;

use Avax\Components\Identity\Tokens\System\Configuration\Assembly\JwtAuthGraph;
use Avax\Components\Identity\Tokens\System\Foundation\Time\Clock;
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

    #[Test]
    public function issuedTokenTimestampsUseInjectedClock(): void
    {
        $clock = new class(1_700_000_000) implements Clock {
            public function __construct(public int $now) {}

            public function now() : int
            {
                return $this->now;
            }
        };

        $jwtAuth = JwtAuthGraph::hmac(secret: 'test-secret', clock: $clock);
        $tokenPair = $jwtAuth->issue(user: ['id' => 'user-1'], scopes: ['read']);

        $introspection = $jwtAuth->introspect(token: $tokenPair->accessToken);

        self::assertTrue($introspection['active']);
        self::assertSame(1_700_000_000, $introspection['iat']);
        self::assertSame(1_700_000_900, $introspection['exp']);
    }

    #[Test]
    public function injectedClockExpiryFailsClosed(): void
    {
        $clock = new class(1_700_000_000) implements Clock {
            public function __construct(public int $now) {}

            public function now() : int
            {
                return $this->now;
            }
        };

        $jwtAuth = JwtAuthGraph::hmac(secret: 'test-secret', clock: $clock);
        $tokenPair = $jwtAuth->issue(user: ['id' => 'user-1'], scopes: ['read']);

        $clock->now = 1_700_000_901;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid token: Expired token');

        $jwtAuth->verify(token: $tokenPair->accessToken);
    }
}
