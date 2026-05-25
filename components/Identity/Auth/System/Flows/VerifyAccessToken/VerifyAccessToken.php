<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\VerifyAccessToken;

use Avax\Components\Identity\Auth\System\Foundation\Failures\TokenRejected;
use Avax\Components\Identity\Auth\System\Foundation\Time\ClockInterface;
use Avax\Components\Identity\Auth\System\Foundation\Values\SignedToken;
use Avax\Components\Identity\Auth\System\Foundation\Values\TokenClaims;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Blacklist\TokenBlacklist;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\VerifyToken;

/**
 * VerifyAccessToken — flow for verifying signed access tokens.
 *
 * Adapted from the enterprise reference package.
 * Checks: (1) signature verification via VerifyToken codec,
 *         (2) expiry via ClockInterface,
 *         (3) blacklist presence via TokenBlacklist.
 * Throws TokenRejected on any failure.
 */
final readonly class VerifyAccessToken
{
    public function __construct(
        private VerifyToken $tokens,
        private TokenBlacklist $blacklist,
        private ClockInterface $clock,
    ) {}

    public function verify(SignedToken $token): VerifiedAccessToken
    {
        $claims = $this->tokens->verify($token);
        if ($claims === null) {
            throw TokenRejected::because('Token signature is invalid.');
        }

        $tokenClaims = TokenClaims::fromPayload($claims);

        if ($tokenClaims->isExpiredAt($this->clock->now())) {
            throw TokenRejected::because('Token is expired.');
        }

        if ($this->blacklist->contains($tokenClaims->tokenId())) {
            throw TokenRejected::because('Token has been revoked.');
        }

        return new VerifiedAccessToken($tokenClaims);
    }
}
