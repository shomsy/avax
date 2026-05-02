<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth;

use Avax\Components\Identity\Auth\System\Capabilities\Tokens\TokenCodec;
use Avax\Components\Identity\Auth\System\Capabilities\Tokens\TokenStore;
use Avax\Tests\TestCase;
use DateTimeImmutable;
use InvalidArgumentException;

final class AuthTokenCapabilityTest extends TestCase
{
    public function test_token_codec_signs_and_rejects_tampered_tokens() : void
    {
        $tokenCodec = new TokenCodec(secret: str_repeat(string: 'c', times: 64));
        $token      = $tokenCodec->encode(payload: ['sub' => 'user-1', 'jti' => 'token-1']);

        self::assertSame(expected: 'user-1', actual: $tokenCodec->decode(token: $token)['sub']);

        $parts    = explode(separator: '.', string: $token);
        $parts[1] = rtrim(string: strtr(base64_encode(string: '{"sub":"user-2"}'), '+/', '-_'), characters: '=');

        $this->expectException(InvalidArgumentException::class);

        $tokenCodec->decode(token: implode(separator: '.', array: $parts));
    }

    public function test_token_store_tracks_revocation_until_expiration() : void
    {
        $tokenStore = new TokenStore();
        $now        = new DateTimeImmutable(datetime: '2026-05-01 12:00:00 UTC');

        $tokenStore->revoke(tokenId: 'token-1', expiresAt: $now->modify(modifier: '+1 hour'));

        self::assertTrue(condition: $tokenStore->isRevoked(tokenId: 'token-1', moment: $now));
        self::assertFalse(condition: $tokenStore->isRevoked(tokenId: 'token-1', moment: $now->modify(modifier: '+2 hours')));
    }
}
