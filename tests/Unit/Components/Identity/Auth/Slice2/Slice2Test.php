<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth\Slice2;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store\IdentitySession;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store\InMemorySessionStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store\RandomSessionId;
use Avax\Components\Identity\Auth\System\Configuration\IdentityConfiguration;
use Avax\Components\Identity\Auth\System\Flows\IssueAccessToken\IssueAccessToken;
use Avax\Components\Identity\Auth\System\Flows\IssueAccessToken\IssueAccessTokenRequest;
use Avax\Components\Identity\Auth\System\Flows\StartSession\StartSession;
use Avax\Components\Identity\Auth\System\Flows\StartSession\StartSessionRequest;
use Avax\Components\Identity\Auth\System\Flows\VerifyAccessToken\VerifyAccessToken;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Auth\System\Foundation\Failures\TokenRejected;
use Avax\Components\Identity\Auth\System\Foundation\Ids\SessionId;
use Avax\Components\Identity\Auth\System\Foundation\Ids\TenantId;
use Avax\Components\Identity\Auth\System\Foundation\Ids\TokenId;
use Avax\Components\Identity\Auth\System\Foundation\Ids\UserId;
use Avax\Components\Identity\Auth\System\Foundation\Time\FrozenClock;
use Avax\Components\Identity\Auth\System\Foundation\Values\SignedToken;
use Avax\Components\Identity\Auth\System\Foundation\Values\TokenClaims;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Blacklist\InMemoryTokenBlacklist;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\HmacTokenCodec;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Slice 2 tests — token issue/verify roundtrip, session lifecycle, blacklist, reset.
 *
 * Tests cover:
 * - Token issue and verify roundtrip
 * - Invalid token rejection
 * - Blacklisted token rejection
 * - Expired token rejection
 * - Session start and retrieve
 * - Expired session rejection
 * - Reset clears state
 */
final class Slice2Test extends TestCase
{
    private const string TEST_SECRET = 'this-is-a-test-secret-that-is-at-least-32-characters-long!';

    // === Token Issue/Verify Roundtrip ===

    public function test_issue_and_verify_access_token_roundtrip(): void
    {
        $clock = new Clock();
        $codec = new HmacTokenCodec(self::TEST_SECRET);
        $blacklist = new InMemoryTokenBlacklist();

        $issueFlow = new IssueAccessToken($codec, $clock);
        $verifyFlow = new VerifyAccessToken($codec, $blacklist, $clock);

        $request = new IssueAccessTokenRequest(
            userId: new UserId('user-123'),
            ttl: new \DateInterval('PT1H'),
            scopes: ['read', 'write'],
        );

        $issued = $issueFlow->issue($request);

        $verified = $verifyFlow->verify($issued->token());
        $this->assertSame('user-123', $verified->claims()->userId()->value);
        $this->assertSame(['read', 'write'], $verified->claims()->scopes());
    }

    public function test_issue_token_includes_tenant(): void
    {
        $clock = new Clock();
        $codec = new HmacTokenCodec(self::TEST_SECRET);
        $blacklist = new InMemoryTokenBlacklist();

        $issueFlow = new IssueAccessToken($codec, $clock);
        $verifyFlow = new VerifyAccessToken($codec, $blacklist, $clock);

        $request = new IssueAccessTokenRequest(
            userId: new UserId('user-456'),
            ttl: new \DateInterval('PT30M'),
            tenantId: new TenantId('tenant-abc'),
        );

        $issued = $issueFlow->issue($request);
        $verified = $verifyFlow->verify($issued->token());

        $this->assertNotNull($verified->claims()->tenantId());
        $this->assertSame('tenant-abc', $verified->claims()->tenantId()->value);
    }

    // === Invalid Token Rejection ===

    public function test_rejects_invalid_signature(): void
    {
        $codec = new HmacTokenCodec(self::TEST_SECRET);
        $blacklist = new InMemoryTokenBlacklist();
        $clock = new Clock();

        $verifyFlow = new VerifyAccessToken($codec, $blacklist, $clock);

        $tamperedToken = SignedToken::fromString('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWUsImlhdCI6MTUxNjIzOTAyMn0.TamperedSignatureHere');

        $this->expectException(TokenRejected::class);
        $this->expectExceptionMessage('Token signature is invalid.');

        $verifyFlow->verify($tamperedToken);
    }

    public function test_rejects_malformed_token(): void
    {
        $codec = new HmacTokenCodec(self::TEST_SECRET);
        $blacklist = new InMemoryTokenBlacklist();
        $clock = new Clock();

        $verifyFlow = new VerifyAccessToken($codec, $blacklist, $clock);

        $malformedToken = SignedToken::fromString('not-a-valid-token');

        $this->expectException(TokenRejected::class);
        $this->expectExceptionMessage('Token signature is invalid.');

        $verifyFlow->verify($malformedToken);
    }

    // === Expired Token Rejection ===

    public function test_rejects_expired_token(): void
    {
        $frozenTime = new DateTimeImmutable('2024-01-01 00:00:00');
        $clock = new FrozenClock($frozenTime);
        $codec = new HmacTokenCodec(self::TEST_SECRET);
        $blacklist = new InMemoryTokenBlacklist();

        $issueFlow = new IssueAccessToken($codec, $clock);

        $request = new IssueAccessTokenRequest(
            userId: new UserId('user-789'),
            ttl: new \DateInterval('PT1H'),
        );

        $issued = $issueFlow->issue($request);

        // Advance time past expiry
        $expiredClock = new FrozenClock($frozenTime->modify('+2 hours'));
        $verifyFlow = new VerifyAccessToken($codec, $blacklist, $expiredClock);

        $this->expectException(TokenRejected::class);
        $this->expectExceptionMessage('Token is expired.');

        $verifyFlow->verify($issued->token());
    }

    // === Blacklisted Token Rejection ===

    public function test_rejects_blacklisted_token(): void
    {
        $clock = new Clock();
        $codec = new HmacTokenCodec(self::TEST_SECRET);
        $blacklist = new InMemoryTokenBlacklist();

        $issueFlow = new IssueAccessToken($codec, $clock);
        $verifyFlow = new VerifyAccessToken($codec, $blacklist, $clock);

        $request = new IssueAccessTokenRequest(
            userId: new UserId('user-revoke'),
            ttl: new \DateInterval('PT1H'),
        );

        $issued = $issueFlow->issue($request);

        // Verify before blacklist
        $verified = $verifyFlow->verify($issued->token());
        $this->assertSame('user-revoke', $verified->claims()->userId()->value);

        // Add to blacklist
        $blacklist->add($verified->claims()->tokenId());

        // Verify after blacklist
        $this->expectException(TokenRejected::class);
        $this->expectExceptionMessage('Token has been revoked.');

        $verifyFlow->verify($issued->token());
    }

    // === Session Lifecycle ===

    public function test_start_and_retrieve_session(): void
    {
        $clock = new Clock();
        $sessionStore = new InMemorySessionStore();
        $sessionIdGenerator = new RandomSessionId();

        $startFlow = new StartSession($sessionIdGenerator, $sessionStore, $clock);

        $request = new StartSessionRequest(
            userId: new UserId('user-session-1'),
            ttl: new \DateInterval('PT24H'),
        );

        $started = $startFlow->start($request);
        $this->assertSame('user-session-1', $started->session()->userId()->value);

        // Retrieve from store
        $retrieved = $sessionStore->find($started->session()->sessionId());
        $this->assertNotNull($retrieved);
        $this->assertSame('user-session-1', $retrieved->userId()->value);
    }

    public function test_rejects_expired_session(): void
    {
        $frozenTime = new DateTimeImmutable('2024-01-01 00:00:00');
        $clock = new FrozenClock($frozenTime);
        $sessionStore = new InMemorySessionStore();
        $sessionIdGenerator = new RandomSessionId();

        $startFlow = new StartSession($sessionIdGenerator, $sessionStore, $clock);

        $request = new StartSessionRequest(
            userId: new UserId('user-expired-session'),
            ttl: new \DateInterval('PT1H'),
        );

        $started = $startFlow->start($request);
        $session = $started->session();

        // Check not expired at start time
        $this->assertFalse($session->isExpiredAt($frozenTime));

        // Check expired after TTL
        $later = $frozenTime->modify('+2 hours');
        $this->assertTrue($session->isExpiredAt($later));
    }

    public function test_session_with_tenant(): void
    {
        $clock = new Clock();
        $sessionStore = new InMemorySessionStore();
        $sessionIdGenerator = new RandomSessionId();

        $startFlow = new StartSession($sessionIdGenerator, $sessionStore, $clock);

        $request = new StartSessionRequest(
            userId: new UserId('user-tenant'),
            ttl: new \DateInterval('PT12H'),
            tenantId: new TenantId('tenant-xyz'),
        );

        $started = $startFlow->start($request);
        $this->assertNotNull($started->session()->tenantId());
        $this->assertSame('tenant-xyz', $started->session()->tenantId()->value);
    }

    public function test_remove_session(): void
    {
        $clock = new Clock();
        $sessionStore = new InMemorySessionStore();
        $sessionIdGenerator = new RandomSessionId();

        $startFlow = new StartSession($sessionIdGenerator, $sessionStore, $clock);

        $request = new StartSessionRequest(
            userId: new UserId('user-remove'),
            ttl: new \DateInterval('PT1H'),
        );

        $started = $startFlow->start($request);
        $sessionId = $started->session()->sessionId();

        $this->assertNotNull($sessionStore->find($sessionId));

        $sessionStore->remove($sessionId);

        $this->assertNull($sessionStore->find($sessionId));
    }

    // === ResettableIdentityState ===

    public function test_reset_clears_blacklist(): void
    {
        $blacklist = new InMemoryTokenBlacklist();
        $tokenId = new TokenId('test-token-id');

        $blacklist->add($tokenId);
        $this->assertTrue($blacklist->contains($tokenId));

        $blacklist->reset();

        $this->assertFalse($blacklist->contains($tokenId));
    }

    public function test_reset_clears_sessions(): void
    {
        $sessionStore = new InMemorySessionStore();
        $sessionId = new SessionId('test-session-id');
        $userId = new UserId('user-reset');
        $now = new DateTimeImmutable();

        $session = new IdentitySession(
            sessionId: $sessionId,
            userId: $userId,
            createdAt: $now,
            expiresAt: $now->modify('+1 hour'),
        );

        $sessionStore->save($session);
        $this->assertNotNull($sessionStore->find($sessionId));

        $sessionStore->reset();

        $this->assertNull($sessionStore->find($sessionId));
    }

    // === TokenClaims serialization ===

    public function test_token_claims_roundtrip_via_payload(): void
    {
        $claims = new TokenClaims(
            tokenId: new TokenId('token-abc'),
            userId: new UserId('user-payload'),
            issuedAt: new DateTimeImmutable('2024-01-01 00:00:00'),
            expiresAt: new DateTimeImmutable('2024-01-01 01:00:00'),
            tenantId: new TenantId('tenant-payload'),
            scopes: ['scope-a', 'scope-b'],
        );

        $payload = $claims->toPayload();
        $restored = TokenClaims::fromPayload($payload);

        $this->assertSame('token-abc', $restored->tokenId()->value);
        $this->assertSame('user-payload', $restored->userId()->value);
        $this->assertNotNull($restored->tenantId());
        $this->assertSame('tenant-payload', $restored->tenantId()->value);
        $this->assertSame(['scope-a', 'scope-b'], $restored->scopes());
        $this->assertFalse($restored->isExpiredAt(new DateTimeImmutable('2024-01-01 00:30:00')));
        $this->assertTrue($restored->isExpiredAt(new DateTimeImmutable('2024-01-01 02:00:00')));
    }

    // === Multi-key codec verification ===

    public function test_different_secrets_reject_token(): void
    {
        $clock = new Clock();
        $codec1 = new HmacTokenCodec(self::TEST_SECRET);
        $codec2 = new HmacTokenCodec('different-secret-that-is-also-at-least-32-chars-long!!');
        $blacklist = new InMemoryTokenBlacklist();

        $issueFlow = new IssueAccessToken($codec1, $clock);
        $verifyFlow = new VerifyAccessToken($codec2, $blacklist, $clock);

        $request = new IssueAccessTokenRequest(
            userId: new UserId('user-wrong-key'),
            ttl: new \DateInterval('PT1H'),
        );

        $issued = $issueFlow->issue($request);

        $this->expectException(TokenRejected::class);
        $this->expectExceptionMessage('Token signature is invalid.');

        $verifyFlow->verify($issued->token());
    }
}
