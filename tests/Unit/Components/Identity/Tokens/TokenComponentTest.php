<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Tokens;

use Avax\Components\Identity\Auth\System\Foundation\Values\SignedToken;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Blacklist\InMemoryTokenBlacklist;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Blacklist\TokenBlacklist;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\HmacTokenCodec;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\TokenCodecInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\MultiKeyHmacTokenCodec;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\InMemoryRefreshTokenStore;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\InMemoryTokenRevocationStore;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\TokenRevocationStoreInterface;
use Avax\Components\Identity\Auth\System\Foundation\Ids\TokenId;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Token component tests — codec, blacklist, stores.
 */
final class TokenComponentTest extends TestCase
{
    // === HmacTokenCodec Tests ===

    public function test_codec_encodes_and_decodes_claims() : void
    {
        $codec = new HmacTokenCodec(secret: 'test-secret-at-least-32-characters!');

        $claims = ['sub' => 'user-1', 'iat' => time(), 'exp' => time() + 3600];
        $encoded = $codec->encode($claims);
        $decoded = $codec->decode($encoded);

        $this->assertNotNull($decoded);
        $this->assertSame('user-1', $decoded['sub']);
        $this->assertSame($claims['iat'], $decoded['iat']);
    }

    public function test_codec_returns_null_for_tampered_token() : void
    {
        $codec = new HmacTokenCodec(secret: 'test-secret-at-least-32-characters!');

        $claims = ['sub' => 'user-1', 'iat' => time(), 'exp' => time() + 3600];
        $encoded = $codec->encode($claims);

        // Tamper with the signature
        $parts = explode('.', $encoded);
        $parts[2] = strrev($parts[2]);
        $tampered = implode('.', $parts);

        $this->assertNull($codec->decode($tampered));
    }

    public function test_codec_returns_null_for_wrong_secret() : void
    {
        $codec1 = new HmacTokenCodec(secret: 'correct-secret-at-least-32-chars!');
        $codec2 = new HmacTokenCodec(secret: 'wrong-secret-at-least-32-chars!!');

        $claims = ['sub' => 'user-1', 'iat' => time(), 'exp' => time() + 3600];
        $encoded = $codec1->encode($claims);

        $this->assertNull($codec2->decode($encoded));
    }

    public function test_codec_rejects_empty_secret() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new HmacTokenCodec(secret: '');
    }

    public function test_codec_rejects_unsupported_algorithm() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new HmacTokenCodec(secret: 'test-secret-at-least-32-characters!', algorithm: 'RS256');
    }

    public function test_codec_supports_hs384() : void
    {
        $codec = new HmacTokenCodec(secret: 'test-secret-at-least-32-characters!', algorithm: 'HS384');

        $claims = ['sub' => 'user-1', 'iat' => time(), 'exp' => time() + 3600];
        $encoded = $codec->encode($claims);
        $decoded = $codec->decode($encoded);

        $this->assertNotNull($decoded);
    }

    public function test_codec_supports_hs512() : void
    {
        $codec = new HmacTokenCodec(secret: 'test-secret-at-least-32-characters!', algorithm: 'HS512');

        $claims = ['sub' => 'user-1', 'iat' => time(), 'exp' => time() + 3600];
        $encoded = $codec->encode($claims);
        $decoded = $codec->decode($encoded);

        $this->assertNotNull($decoded);
    }

    public function test_codec_returns_null_for_malformed_token() : void
    {
        $codec = new HmacTokenCodec(secret: 'test-secret-at-least-32-characters!');

        $this->assertNull($codec->decode('not-a-jwt'));
        $this->assertNull($codec->decode('only.two'));
        $this->assertNull($codec->decode(''));
    }

    public function test_codec_sign_token_interface() : void
    {
        $codec = new HmacTokenCodec(secret: 'test-secret-at-least-32-characters!');

        $claims = ['sub' => 'workload-1', 'iat' => time()];
        $signed = $codec->sign($claims);

        $this->assertTrue(is_a($signed, SignedToken::class, true));

        $verified = $codec->verify($signed);
        $this->assertNotNull($verified);
        $this->assertSame('workload-1', $verified['sub']);
    }

    public function test_codec_verify_returns_null_for_wrong_key() : void
    {
        $codec1 = new HmacTokenCodec(secret: 'correct-secret-at-least-32-chars!');
        $codec2 = new HmacTokenCodec(secret: 'wrong-secret-at-least-32-chars!!');

        $claims = ['sub' => 'user-1'];
        $signed = $codec1->sign($claims);

        $this->assertNull($codec2->verify($signed));
    }

    public function test_codec_includes_key_id_when_provided() : void
    {
        $codec = new HmacTokenCodec(secret: 'test-secret-at-least-32-characters!', keyId: 'key-2024-01');

        $claims = ['sub' => 'user-1'];
        $encoded = $codec->encode($claims);
        $decoded = $codec->decode($encoded);

        $this->assertNotNull($decoded);
    }

    public function test_codec_rejects_token_with_wrong_key_id() : void
    {
        $codec1 = new HmacTokenCodec(secret: 'test-secret-at-least-32-characters!', keyId: 'key-a');
        $codec2 = new HmacTokenCodec(secret: 'test-secret-at-least-32-characters!', keyId: 'key-b');

        $claims = ['sub' => 'user-1'];
        $encoded = $codec1->encode($claims);

        $this->assertNull($codec2->decode($encoded));
    }

    // === MultiKeyHmacTokenCodec Tests ===

    public function test_multi_key_codec_decodes_with_matching_verification_codec() : void
    {
        $signingCodec = new HmacTokenCodec(secret: 'signing-secret-at-least-32-chars!');
        $verificationCodec = new HmacTokenCodec(secret: 'signing-secret-at-least-32-chars!');

        $multiCodec = new MultiKeyHmacTokenCodec(
            tokenCodec: $signingCodec,
            verificationCodecs: [$verificationCodec],
        );

        $claims = ['sub' => 'user-1', 'iat' => time()];
        $encoded = $multiCodec->encode($claims);
        $decoded = $multiCodec->decode($encoded);

        $this->assertNotNull($decoded);
        $this->assertSame('user-1', $decoded['sub']);
    }

    public function test_multi_key_codec_uses_primary_for_encoding() : void
    {
        $primary = new HmacTokenCodec(secret: 'primary-secret-at-least-32-chars!');
        $secondary = new HmacTokenCodec(secret: 'secondary-secret-at-least-32-chars!');

        $multiCodec = new MultiKeyHmacTokenCodec(
            tokenCodec: $primary,
            verificationCodecs: [$primary, $secondary],
        );

        $claims = ['sub' => 'user-1'];
        $encoded = $multiCodec->encode($claims);

        // Primary can decode its own tokens
        $this->assertNotNull($primary->decode($encoded));
    }

    // === InMemoryTokenBlacklist Tests ===

    public function test_blacklist_contains_after_add() : void
    {
        $blacklist = new InMemoryTokenBlacklist();
        $tokenId = new TokenId('token-123');

        $this->assertFalse($blacklist->contains($tokenId));
        $blacklist->add($tokenId);
        $this->assertTrue($blacklist->contains($tokenId));
    }

    public function test_blacklist_interface_type() : void
    {
        $blacklist = new InMemoryTokenBlacklist();
        $this->assertInstanceOf(TokenBlacklist::class, $blacklist);
    }

    public function test_blacklist_reset() : void
    {
        $blacklist = new InMemoryTokenBlacklist();
        $tokenId = new TokenId('token-123');

        $blacklist->add($tokenId);
        $this->assertTrue($blacklist->contains($tokenId));

        $blacklist->reset();
        $this->assertFalse($blacklist->contains($tokenId));
    }

    // === InMemoryTokenRevocationStore Tests ===

    public function test_revocation_store_marks_and_checks_revoked() : void
    {
        $store = new InMemoryTokenRevocationStore();
        $tokenId = 'token-abc';
        $expiresAt = new DateTimeImmutable('+1 hour');

        $this->assertFalse($store->isRevoked($tokenId, new DateTimeImmutable()));
        $store->revoke($tokenId, $expiresAt);
        $this->assertTrue($store->isRevoked($tokenId, new DateTimeImmutable()));
    }

    public function test_revocation_store_interface_type() : void
    {
        $store = new InMemoryTokenRevocationStore();
        $this->assertInstanceOf(TokenRevocationStoreInterface::class, $store);
    }

    public function test_revocation_store_forgets_expired_tokens() : void
    {
        $store = new InMemoryTokenRevocationStore();
        $tokenId = 'token-expired';
        $expiresAt = new DateTimeImmutable('-1 hour');

        $store->revoke($tokenId, $expiresAt);

        // Token is expired, should be considered not revoked (cleaned up)
        $this->assertFalse($store->isRevoked($tokenId, new DateTimeImmutable()));
    }

    // === InMemoryRefreshTokenStore Tests ===

    public function test_refresh_token_store_issues_token() : void
    {
        $store = new InMemoryRefreshTokenStore();
        $userId = new \Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId(1);
        $expiresAt = new DateTimeImmutable('+30 days');

        $issued = $store->issue(
            userId: $userId,
            expiresAt: $expiresAt,
        );

        $this->assertNotEmpty($issued->token);
        $this->assertNotEmpty($issued->tokenId);
        $this->assertTrue($issued->expiresAt->getTimestamp() > time());
    }

    public function test_refresh_token_store_finds_issued_token() : void
    {
        $store = new InMemoryRefreshTokenStore();
        $userId = new \Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId(1);
        $expiresAt = new DateTimeImmutable('+30 days');

        $issued = $store->issue(userId: $userId, expiresAt: $expiresAt);
        $found = $store->find($issued->token);

        $this->assertNotNull($found);
        $this->assertSame($issued->tokenId, $found->tokenId);
    }

    public function test_refresh_token_store_returns_null_for_unknown_token() : void
    {
        $store = new InMemoryRefreshTokenStore();

        $this->assertNull($store->find('unknown-token'));
    }

    public function test_refresh_token_store_interface_type() : void
    {
        $store = new InMemoryRefreshTokenStore();
        $this->assertInstanceOf(RefreshTokenStoreInterface::class, $store);
    }

    public function test_refresh_token_store_mark_rotated() : void
    {
        $store = new InMemoryRefreshTokenStore();
        $userId = new \Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId(1);
        $expiresAt = new DateTimeImmutable('+30 days');

        $issued = $store->issue(userId: $userId, expiresAt: $expiresAt);
        $store->markRotated($issued->tokenId, 'replacement-token-id');

        // After rotation, the original token should still be findable but marked
        $found = $store->find($issued->token);
        $this->assertNotNull($found);
        $this->assertTrue($found->wasRotated());
    }

    public function test_refresh_token_store_revoke_user() : void
    {
        $store = new InMemoryRefreshTokenStore();
        $userId = new \Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId(1);
        $expiresAt = new DateTimeImmutable('+30 days');

        $issued = $store->issue(userId: $userId, expiresAt: $expiresAt);
        $store->revokeUser($userId);

        // After user revocation, token should not be found
        $found = $store->find($issued->token);
        $this->assertNull($found);
    }
}
