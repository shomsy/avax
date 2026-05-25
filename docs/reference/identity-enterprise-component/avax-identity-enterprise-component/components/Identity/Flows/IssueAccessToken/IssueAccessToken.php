<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\IssueAccessToken;

use Avax\Components\Identity\Capabilities\Tokens\SignToken;
use Avax\Components\Identity\Capabilities\Tokens\TokenClaims;
use Avax\Components\Identity\Foundation\Time\Clock;
use Avax\Components\Identity\Foundation\Values\TokenId;

final readonly class IssueAccessToken
{
    public function __construct(private SignToken $tokens, private Clock $clock) {}

    public function issue(IssueAccessTokenRequest $request): IssuedAccessToken
    {
        $now = $this->clock->now();
        $claims = new TokenClaims(
            tokenId: TokenId::random(),
            userId: $request->userId(),
            issuedAt: $now,
            expiresAt: $now->add($request->ttl()),
            tenantId: $request->tenantId(),
            scopes: $request->scopes(),
        );

        return new IssuedAccessToken($this->tokens->sign($claims));
    }
}
