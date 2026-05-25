<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\VerifyAccessToken;

use Avax\Components\Identity\Capabilities\Tokens\TokenBlacklist;
use Avax\Components\Identity\Capabilities\Tokens\VerifyToken;
use Avax\Components\Identity\Foundation\Failures\TokenRejected;
use Avax\Components\Identity\Foundation\Time\Clock;
use Avax\Components\Identity\Foundation\Values\SignedToken;

final readonly class VerifyAccessToken
{
    public function __construct(private VerifyToken $tokens, private TokenBlacklist $blacklist, private Clock $clock) {}

    public function verify(SignedToken $token): VerifiedAccessToken
    {
        $claims = $this->tokens->verify($token);
        if ($claims->isExpiredAt($this->clock->now())) {
            throw TokenRejected::because('Token is expired.');
        }

        if ($this->blacklist->contains($claims->tokenId())) {
            throw TokenRejected::because('Token has been revoked.');
        }

        return new VerifiedAccessToken($claims);
    }
}
