<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Identity\Jwt;

use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\Token\IssuedRefreshToken;
use Avax\Auth\System\Flow\Token\IssuedToken;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Flow\Token\ResolvedToken;
use Avax\Auth\System\Flow\Token\TokenCodecInterface;
use Avax\Auth\System\Flow\Token\TokenRevocationStoreInterface;
use DateTimeImmutable;
use InvalidArgumentException;
use SensitiveParameter;
use Throwable;

/**
 * JWT identity implementation within the Auth System.
 */
final readonly class JwtIdentity implements JwtIdentityInterface
{
    public function __construct(
        private UserSourceInterface                $userSource,
        private TokenCodecInterface                $codec,
        private \Avax\Auth\System\Foundation\Clock $clock,
        private TokenRevocationStoreInterface|null $revocationStore = null,
        private RefreshTokenStoreInterface|null    $refreshTokenStore = null,
        private int                                $tokenExpiry = 3600,
        private int                                $refreshTokenExpiry = 2_592_000,
        private string                             $issuer = 'avax-auth-system'
    ) {}

    public function resolve(#[SensitiveParameter] string $token) : ResolvedToken|null
    {
        try {
            $claims = $this->codec->decode($token);

            if ($claims === null) {
                return null;
            }

            $expiresAt = $claims['exp'] ?? null;
            $issuedAt  = $claims['iat'] ?? null;
            $notBefore = $claims['nbf'] ?? null;
            $subject   = $claims['sub'] ?? null;
            $tokenId   = $claims['jti'] ?? null;

            if (
                ! is_int($expiresAt)
                || ! is_int($issuedAt)
                || ! is_int($notBefore)
                || ! is_int($subject)
                || ! is_string($tokenId)
            ) {
                return null;
            }

            $now = $this->clock->now();

            if ($issuedAt > $now->getTimestamp() || $notBefore > $now->getTimestamp() || $expiresAt <= $now->getTimestamp()) {
                return null;
            }

            $expiryMoment = new DateTimeImmutable("@{$expiresAt}");

            if ($this->revocationStore?->isRevoked($tokenId, $now) === true) {
                return null;
            }

            $mfaVerifiedAt = null;
            $mfaTimestamp  = $claims['mfa_at'] ?? null;
            $phishingResistant = ($claims['phr'] ?? 0) === 1;
            $clientId      = $claims['client_id'] ?? null;
            $scopeClaim    = $claims['scope'] ?? null;
            $scopes        = [];

            if (is_int($mfaTimestamp)) {
                $mfaVerifiedAt = new DateTimeImmutable("@{$mfaTimestamp}");
            }

            if (! is_string($clientId) && $clientId !== null) {
                return null;
            }

            if (is_string($scopeClaim) && $scopeClaim !== '') {
                $scopes = array_values(array_filter(explode(' ', $scopeClaim), static fn (string $scope) : bool => $scope !== ''));
            }

            $user = $this->userSource->findById(new UserId($subject));

            if ($user === null || ! $user->isActive()) {
                return null;
            }

            return new ResolvedToken(
                user         : $user,
                tokenId      : $tokenId,
                expiresAt    : $expiryMoment,
                mfaVerifiedAt: $mfaVerifiedAt,
                phishingResistant: $phishingResistant,
                clientId     : $clientId,
                scopes       : $scopes
            );
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param list<string> $scopes
     */
    public function issueRefreshToken(
        User $user,
        DateTimeImmutable|null $mfaVerifiedAt = null,
        bool $phishingResistant = false,
        string|null $clientId = null,
        array $scopes = []
    ) : IssuedRefreshToken|null
    {
        if ($this->refreshTokenStore === null) {
            return null;
        }

        return $this->refreshTokenStore->issue(
            userId       : $user->getId(),
            expiresAt    : $this->clock->now()->modify("+{$this->refreshTokenExpiry} seconds"),
            mfaVerifiedAt: $mfaVerifiedAt,
            phishingResistant: $phishingResistant,
            clientId     : $clientId,
            scopes       : $scopes
        );
    }

    /**
     * @param list<string> $scopes
     */
    public function issue(
        User $user,
        DateTimeImmutable|null $mfaVerifiedAt = null,
        bool $phishingResistant = false,
        string|null $clientId = null,
        array $scopes = []
    ) : IssuedToken
    {
        if (! $user->isActive()) {
            throw new InvalidArgumentException(message: 'Inactive users cannot be authenticated.');
        }

        $issuedAt  = $this->clock->now();
        $expiresAt = $issuedAt->modify("+{$this->tokenExpiry} seconds");
        $tokenId   = bin2hex(random_bytes(16));
        $payload   = [
            'iss' => $this->issuer,
            'sub' => $user->getId()->value,
            'iat' => $issuedAt->getTimestamp(),
            'nbf' => $issuedAt->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
            'jti' => $tokenId,
        ];

        if ($mfaVerifiedAt !== null) {
            $payload['mfa_at'] = $mfaVerifiedAt->getTimestamp();
        }

        if ($phishingResistant) {
            $payload['phr'] = 1;
        }

        if ($clientId !== null) {
            $payload['client_id'] = $clientId;
        }

        if ($scopes !== []) {
            $payload['scope'] = implode(' ', array_values($scopes));
        }

        return new IssuedToken(
            token    : $this->codec->encode($payload),
            tokenId  : $tokenId,
            expiresAt: $expiresAt
        );
    }

    public function revoke(string $tokenId, DateTimeImmutable $expiresAt) : void
    {
        $this->revocationStore?->revoke($tokenId, $expiresAt);
    }
}
