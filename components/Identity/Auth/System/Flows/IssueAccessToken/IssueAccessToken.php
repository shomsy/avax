<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\IssueAccessToken;

use Avax\Components\Identity\Auth\System\Foundation\Time\ClockInterface;
use Avax\Components\Identity\Auth\System\Foundation\Values\TokenClaims;
use Avax\Components\Identity\Auth\System\Foundation\Ids\TokenId;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\SignToken;

/**
 * IssueAccessToken — flow for issuing signed access tokens.
 *
 * Adapted from the enterprise reference package.
 * Takes a request DTO, builds TokenClaims, signs them via SignToken codec,
 * and returns an IssuedAccessToken value object.
 */
final readonly class IssueAccessToken
{
    public function __construct(
        private SignToken $tokens,
        private ClockInterface $clock,
    ) {}

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

        return new IssuedAccessToken($this->tokens->sign($claims->toPayload()));
    }
}
