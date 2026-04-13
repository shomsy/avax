<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Identity\Jwt;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\Token\IssuedRefreshToken;
use Avax\Auth\System\Flow\Token\IssuedToken;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Flow\Token\ResolvedToken;
use Avax\Auth\System\Flow\Token\ResolvedWorkloadToken;
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
        private UserSourceInterface                                    $userSource,
        private TokenCodecInterface                                    $codec,
        private \Avax\Auth\System\Foundation\Clock                     $clock,
        private TokenRevocationStoreInterface|null                     $revocationStore = null,
        #[\SensitiveParameter] private RefreshTokenStoreInterface|null $refreshTokenStore = null,
        private int                                                    $tokenExpiry = 3600,
        private int                                                    $refreshTokenExpiry = 2_592_000,
        private string                                                 $issuer = 'avax-auth-system'
    ) {}

    public function resolve(#[SensitiveParameter] string $token) : ResolvedToken|null
    {
        try {
            $claims = $this->codec->decode(token: $token);

            if ($claims === null) {
                return null;
            }

            $expiresAt = $claims['exp'] ?? null;
            $issuedAt  = $claims['iat'] ?? null;
            $notBefore = $claims['nbf'] ?? null;
            $subject   = $claims['sub'] ?? null;
            $tokenId   = $claims['jti'] ?? null;
            $issuer    = $claims['iss'] ?? null;

            if (
                ! is_string($issuer)
                || $issuer !== $this->issuer
                || ! is_int($expiresAt)
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

            $expiryMoment = new DateTimeImmutable(datetime: "@{$expiresAt}");

            if ($this->revocationStore?->isRevoked(tokenId: $tokenId, moment: $now) === true) {
                return null;
            }

            $mfaVerifiedAt = null;
            $mfaTimestamp  = $claims['mfa_at'] ?? null;
            $phishingResistant = ($claims['phr'] ?? 0) === 1;
            $clientId      = $claims['client_id'] ?? null;
            $scopeClaim    = $claims['scope'] ?? null;
            $senderConstraintType = $claims['cnf_typ'] ?? null;
            $senderConstraintThumbprint = $claims['cnf_thumbprint'] ?? null;
            $scopes        = [];
            $senderConstraint = null;

            if (is_int($mfaTimestamp)) {
                $mfaVerifiedAt = new DateTimeImmutable(datetime: "@{$mfaTimestamp}");
            }

            if (! is_string($clientId) && $clientId !== null) {
                return null;
            }

            if (is_string($scopeClaim) && $scopeClaim !== '') {
                $scopes = array_values(array_filter(explode(' ', $scopeClaim), static fn (string $scope) : bool => $scope !== ''));
            }

            if ($senderConstraintType !== null || $senderConstraintThumbprint !== null) {
                if (! is_string($senderConstraintType) || ! is_string($senderConstraintThumbprint)) {
                    return null;
                }

                $senderConstraint = new OAuthSenderConstraint(
                    type      : OAuthSenderConstraintType::from(value: $senderConstraintType),
                    thumbprint: $senderConstraintThumbprint
                );
            }

            $user = $this->userSource->findById(id: new UserId(value: $subject));

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
                scopes       : $scopes,
                senderConstraint: $senderConstraint
            );
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param list<string> $scopes
     */
    public function issueWorkloadToken(
        string $subject,
        string $clientId,
        array $scopes = [],
        OAuthSenderConstraint|null $senderConstraint = null,
        string|null $audience = null
    ) : IssuedToken
    {
        $normalizedSubject = trim($subject);

        if ($normalizedSubject === '') {
            throw new InvalidArgumentException(message: 'Workload token subject cannot be empty.');
        }

        $issuedAt  = $this->clock->now();
        $expiresAt = $issuedAt->modify(modifier: "+{$this->tokenExpiry} seconds");
        $tokenId   = bin2hex(random_bytes(16));
        $payload   = [
            'iss' => $this->issuer,
            'sub' => $normalizedSubject,
            'iat' => $issuedAt->getTimestamp(),
            'nbf' => $issuedAt->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
            'jti' => $tokenId,
            'wli' => 1,
            'client_id' => $clientId,
        ];

        if ($scopes !== []) {
            $payload['scope'] = implode(' ', array_values($scopes));
        }

        if ($audience !== null && trim($audience) !== '') {
            $payload['aud'] = trim($audience);
        }

        if ($senderConstraint !== null) {
            $payload['cnf_typ'] = $senderConstraint->type->value;
            $payload['cnf_thumbprint'] = $senderConstraint->thumbprint;
        }

        return new IssuedToken(
            token    : $this->codec->encode(claims: $payload),
            tokenId  : $tokenId,
            expiresAt: $expiresAt
        );
    }

    public function resolveWorkloadToken(
        #[SensitiveParameter] string $token,
        string|null $expectedAudience = null,
        string|null $expectedIssuer = null
    ) : ResolvedWorkloadToken|null
    {
        try {
            $claims = $this->codec->decode(token: $token);

            if ($claims === null) {
                return null;
            }

            $expiresAt = $claims['exp'] ?? null;
            $issuedAt  = $claims['iat'] ?? null;
            $notBefore = $claims['nbf'] ?? null;
            $subject   = $claims['sub'] ?? null;
            $tokenId   = $claims['jti'] ?? null;
            $clientId  = $claims['client_id'] ?? null;
            $issuer    = $claims['iss'] ?? null;
            $audience  = $claims['aud'] ?? null;
            $workloadIdentity = ($claims['wli'] ?? 0) === 1;
            $scopeClaim = $claims['scope'] ?? null;
            $senderConstraintType = $claims['cnf_typ'] ?? null;
            $senderConstraintThumbprint = $claims['cnf_thumbprint'] ?? null;
            $scopes = [];
            $senderConstraint = null;

            if (
                ! $workloadIdentity
                || ! is_int($expiresAt)
                || ! is_int($issuedAt)
                || ! is_int($notBefore)
                || ! is_string($subject)
                || trim($subject) === ''
                || ! is_string($tokenId)
                || ! is_string($clientId)
                || ! is_string($issuer)
            ) {
                return null;
            }

            if ($issuer !== ($expectedIssuer ?? $this->issuer)) {
                return null;
            }

            if ($audience !== null && ! is_string($audience)) {
                return null;
            }

            if ($expectedAudience !== null && $audience !== $expectedAudience) {
                return null;
            }

            $now = $this->clock->now();

            if ($issuedAt > $now->getTimestamp() || $notBefore > $now->getTimestamp() || $expiresAt <= $now->getTimestamp()) {
                return null;
            }

            if ($this->revocationStore?->isRevoked(tokenId: $tokenId, moment: $now) === true) {
                return null;
            }

            if (is_string($scopeClaim) && $scopeClaim !== '') {
                $scopes = array_values(array_filter(explode(' ', $scopeClaim), static fn (string $scope) : bool => $scope !== ''));
            }

            if ($senderConstraintType !== null || $senderConstraintThumbprint !== null) {
                if (! is_string($senderConstraintType) || ! is_string($senderConstraintThumbprint)) {
                    return null;
                }

                $senderConstraint = new OAuthSenderConstraint(
                    type      : OAuthSenderConstraintType::from(value: $senderConstraintType),
                    thumbprint: $senderConstraintThumbprint
                );
            }

            return new ResolvedWorkloadToken(
                subject          : $subject,
                clientId         : $clientId,
                tokenId          : $tokenId,
                expiresAt        : new DateTimeImmutable(datetime: "@{$expiresAt}"),
                scopes           : $scopes,
                audience         : $audience,
                issuer           : $issuer,
                senderConstraint : $senderConstraint
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
        array $scopes = [],
        OAuthSenderConstraint|null $senderConstraint = null
    ) : IssuedRefreshToken|null
    {
        if ($this->refreshTokenStore === null) {
            return null;
        }

        return $this->refreshTokenStore->issue(
            userId       : $user->getId(),
            expiresAt    : $this->clock->now()->modify(modifier: "+{$this->refreshTokenExpiry} seconds"),
            mfaVerifiedAt: $mfaVerifiedAt,
            phishingResistant: $phishingResistant,
            clientId     : $clientId,
            scopes       : $scopes,
            senderConstraint: $senderConstraint
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
        array $scopes = [],
        OAuthSenderConstraint|null $senderConstraint = null
    ) : IssuedToken
    {
        if (! $user->isActive()) {
            throw new InvalidArgumentException(message: 'Inactive users cannot be authenticated.');
        }

        $issuedAt  = $this->clock->now();
        $expiresAt = $issuedAt->modify(modifier: "+{$this->tokenExpiry} seconds");
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

        if ($senderConstraint !== null) {
            $payload['cnf_typ'] = $senderConstraint->type->value;
            $payload['cnf_thumbprint'] = $senderConstraint->thumbprint;
        }

        return new IssuedToken(
            token    : $this->codec->encode(claims: $payload),
            tokenId  : $tokenId,
            expiresAt: $expiresAt
        );
    }

    public function revoke(#[\SensitiveParameter] string $tokenId, DateTimeImmutable $expiresAt) : void
    {
        $this->revocationStore?->revoke(tokenId: $tokenId, expiresAt: $expiresAt);
    }
}
