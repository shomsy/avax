<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt;

use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Issuer\TokenIssuerInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedRefreshToken;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedToken;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\ResolvedToken;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\ResolvedWorkloadToken;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Verify\TokenVerifierInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
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
        User                  $user,
        DateTimeImmutable     $mfaVerifiedAt = null,
        bool                  $phishingResistant = false,
        string                $clientId = null,
        array                 $scopes = [],
        string                $refreshTokenFamilyId = null,
        OAuthSenderConstraint $senderConstraint = null,
    ) : IssuedToken;

    public function resolve(string $token) : ResolvedToken|null;

    /**
     * @param list<string> $scopes
     */
    public function issueWorkloadToken(
        string                $subject,
        string                $clientId,
        array                 $scopes = [],
        OAuthSenderConstraint $senderConstraint = null,
        string                $audience = null,
    ) : IssuedToken;

    public function resolveWorkloadToken(
        string $token,
        string $expectedAudience = null,
        string $expectedIssuer = null,
    ) : ResolvedWorkloadToken|null;

    /**
     * @param list<string> $scopes
     */
    public function issueRefreshToken(
        User                  $user,
        DateTimeImmutable     $mfaVerifiedAt = null,
        bool                  $phishingResistant = false,
        string                $clientId = null,
        array                 $scopes = [],
        OAuthSenderConstraint $senderConstraint = null,
    ) : IssuedRefreshToken|null;

    public function revoke(string $tokenId, DateTimeImmutable $expiresAt) : void;
}
