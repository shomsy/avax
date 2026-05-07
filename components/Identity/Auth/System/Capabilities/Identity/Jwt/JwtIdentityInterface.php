<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraint;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Issuer\TokenIssuerInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\IssuedRefreshToken;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\IssuedToken;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\ResolvedToken;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\ResolvedWorkloadToken;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Verify\TokenVerifierInterface;
use DateTimeImmutable;

/**
 * Interface JwtIdentityInterface within the Auth System.
 */
interface JwtIdentityInterface extends TokenIssuerInterface, TokenVerifierInterface
{
    /**
     * @param list<string> $scopes
     */
    public function issue(
        User                   $user,
        ?DateTimeImmutable     $issuedAt = null,
        bool                   $phishingResistant = false,
        ?string                $audience = null,
        array                  $scopes = [],
        ?string                $issuer = null,
        ?OAuthSenderConstraint $oAuthSenderConstraint = null,
    ) : IssuedToken;

    public function resolve(string $token) : ?ResolvedToken;

    /**
     * @param list<string> $scopes
     */
    public function issueWorkloadToken(
        string                 $subject,
        string                 $clientId,
        array                  $scopes = [],
        ?OAuthSenderConstraint $oAuthSenderConstraint = null,
        ?string                $audience = null,
    ) : IssuedToken;

    public function resolveWorkloadToken(
        string  $token,
        ?string $expectedAudience = null,
        ?string $expectedIssuer = null,
    ) : ?ResolvedWorkloadToken;

    /**
     * @param list<string> $scopes
     */
    public function issueRefreshToken(
        User                   $user,
        ?DateTimeImmutable     $issuedAt = null,
        bool                   $phishingResistant = false,
        ?string                $audience = null,
        array                  $scopes = [],
        ?OAuthSenderConstraint $oAuthSenderConstraint = null,
    ) : ?IssuedRefreshToken;

    public function revoke(string $tokenId, DateTimeImmutable $expiresAt) : void;
}
