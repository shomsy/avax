<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Configuration\Assembly;

use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\JwtAuth;
use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Signing\JwtSigner;
use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\TokenBlacklist;
use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Verification\TokenVerifier;
use Avax\Components\Identity\Tokens\System\Foundation\Time\Clock;
use Avax\Components\Identity\Tokens\System\Foundation\Time\SystemClock;

/**
 * Assembles JwtAuth runtime instances from explicit signing configuration.
 */
final class JwtAuthGraph
{
    public static function hmac(string $secret, string $algo = 'HS256', ?Clock $clock = null) : JwtAuth
    {
        $jwtSigner = new JwtSigner(secret: $secret, algo: $algo);
        $clock ??= new SystemClock();

        return new JwtAuth(
            jwtSigner     : $jwtSigner,
            tokenVerifier : new TokenVerifier(jwtSigner: $jwtSigner, clock: $clock),
            tokenBlacklist: new TokenBlacklist(),
            clock         : $clock,
        );
    }
}
