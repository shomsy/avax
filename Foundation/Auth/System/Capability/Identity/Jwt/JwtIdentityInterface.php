<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Identity\Jwt;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Flow\Token\IssuedRefreshToken;
use Avax\Auth\System\Flow\Token\IssuedToken;
use Avax\Auth\System\Flow\Token\ResolvedToken;
use Avax\Auth\System\Flow\Token\ResolvedWorkloadToken;
use Avax\Auth\System\Flow\Token\TokenIssuerInterface;
use Avax\Auth\System\Flow\Token\TokenVerifierInterface;
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
        User $user,
        DateTimeImmutable|null $mfaVerifiedAt = null,
        bool $phishingResistant = false,
        string|null $clientId = null,
        array $scopes = [],
        OAuthSenderConstraint|null $senderConstraint = null,
        string|null $refreshTokenFamilyId = null
    ) : IssuedToken;

    public function resolve(string $token) : ResolvedToken|null;

    /**
     * @param list<string> $scopes
     */
    public function issueWorkloadToken(
        string $subject,
        string $clientId,
        array $scopes = [],
        OAuthSenderConstraint|null $senderConstraint = null,
        string|null $audience = null
    ) : IssuedToken;

    public function resolveWorkloadToken(
        string $token,
        string|null $expectedAudience = null,
        string|null $expectedIssuer = null
    ) : ResolvedWorkloadToken|null;

    /**
     * @param list<string> $scopes
     */
    public function issueRefreshToken(
        User $user,
        DateTimeImmutable|null $mfaVerifiedAt = null,
        bool $phishingResistant = false,
        string|null $clientId = null,
        array $scopes = [],
        OAuthSenderConstraint|null $senderConstraint = null
    ) : IssuedRefreshToken|null;

    public function revoke(string $tokenId, DateTimeImmutable $expiresAt) : void;
}
