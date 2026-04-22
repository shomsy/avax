<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Jwt;

use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec\TokenCodecInterface;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedRefreshToken;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedToken;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\ResolvedToken;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\ResolvedWorkloadToken;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\TokenRevocationStoreInterface;
use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use DateTimeImmutable;
use InvalidArgumentException;
use Random\RandomException;
use SensitiveParameter;
use Throwable;

/**
 * JWT identity implementation within the Auth System.
 */
final readonly class JwtIdentity implements JwtIdentityInterface
{
    private string                             $issuer;
    private int                                $refreshTokenExpiry;
    private int                                $tokenExpiry;

    public function __construct(
        private UserSourceInterface                                   $userSource,
        private TokenCodecInterface                                   $codec,
        private Clock                                                 $clock,
        private TokenRevocationStoreInterface|null                    $revocationStore = null,
        #[SensitiveParameter] private RefreshTokenStoreInterface|null $refreshTokenStore = null,
        int|null                                              $tokenExpiry = null,
        int|null                                              $refreshTokenExpiry = null,
        string|null                                           $issuer = null,
        private int                                                   $leeway = 60
    )
    {
        $tokenExpiry              ??= 3600;
        $refreshTokenExpiry       ??= 2_592_000;
        $issuer                   ??= 'avax-auth-system';
        $this->tokenExpiry        = $tokenExpiry;
        $this->refreshTokenExpiry = $refreshTokenExpiry;
        $this->issuer             = $issuer;
    }

    public function verify(#[SensitiveParameter] string $token) : bool
    {
        return $this->resolve(token: $token) !== null;
    }

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
                ! is_string(value: $issuer)
                || $issuer !== $this->issuer
                || ! is_int(value: $expiresAt)
                || ! is_int(value: $issuedAt)
                || ! is_int(value: $notBefore)
                || ! is_scalar(value: $subject)
                || ! is_string(value: $tokenId)
            ) {
                return null;
            }

            $nowTimestamp = $this->clock->now()->getTimestamp();

            if ($issuedAt > ($nowTimestamp + $this->leeway) || $notBefore > ($nowTimestamp + $this->leeway) || $expiresAt <= ($nowTimestamp - $this->leeway)) {
                return null;
            }

            $expiryMoment = new DateTimeImmutable(datetime: "@{$expiresAt}");

            if ($this->revocationStore?->isRevoked(tokenId: $tokenId, moment: $this->clock->now()) === true) {
                return null;
            }

            $mfaVerifiedAt              = null;
            $mfaTimestamp               = $claims['mfa_at'] ?? null;
            $phishingResistant          = ($claims['phr'] ?? 0) === 1;
            $clientId                   = $claims['client_id'] ?? null;
            $scopeClaim                 = $claims['scope'] ?? null;
            $senderConstraintType       = $claims['cnf_typ'] ?? null;
            $senderConstraintThumbprint = $claims['cnf_thumbprint'] ?? null;
            $familyId                   = $claims['fid'] ?? null;
            $scopes                     = [];
            $senderConstraint           = null;

            if (is_int(value: $mfaTimestamp)) {
                $mfaVerifiedAt = new DateTimeImmutable(datetime: "@{$mfaTimestamp}");
            }

            if (! is_string(value: $clientId) && $clientId !== null) {
                return null;
            }

            if (is_string(value: $scopeClaim) && $scopeClaim !== '') {
                $scopes = array_values(array: array_filter(
                                           array   : explode(separator: ' ', string: $scopeClaim),
                                           callback: static fn (string $scope) : bool => $scope !== ''
                ));
            }

            if ($senderConstraintType !== null || $senderConstraintThumbprint !== null) {
                if (! is_string(value: $senderConstraintType) || ! is_string(value: $senderConstraintThumbprint)) {
                    return null;
                }

                $senderConstraint = new OAuthSenderConstraint(
                    type      : OAuthSenderConstraintType::from(value: $senderConstraintType),
                    thumbprint: $senderConstraintThumbprint
                );
            }

            $user = $this->userSource->findById(id: new UserId(value: (int) $subject));

            if ($user === null || ! $user->isActive()) {
                return null;
            }

            return new ResolvedToken(
                user             : $user,
                tokenId          : $tokenId,
                expiresAt        : $expiryMoment,
                mfaVerifiedAt    : $mfaVerifiedAt,
                phishingResistant: $phishingResistant,
                clientId         : $clientId,
                scopes           : $scopes,
                senderConstraint : $senderConstraint,
                familyId         : is_string(value: $familyId) ? $familyId : null
            );
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param list<string> $scopes
     *
     * @throws RandomException
     * @throws DateMalformedStringException
     */
    public function issueWorkloadToken(
        string                     $subject,
        string                     $clientId,
        array                      $scopes = [],
        OAuthSenderConstraint|null $senderConstraint = null,
        string|null                $audience = null
    ) : IssuedToken
    {
        $normalizedSubject = trim(string: $subject);

        if ($normalizedSubject === '') {
            throw new InvalidArgumentException(message: 'Workload token subject cannot be empty.');
        }

        $issuedAt  = $this->clock->now();
        $expiresAt = $issuedAt->modify(modifier: "+{$this->tokenExpiry} seconds");
        $tokenId   = bin2hex(string: random_bytes(length: 16));
        $payload   = [
            'iss'       => $this->issuer,
            'sub'       => $normalizedSubject,
            'iat'       => $issuedAt->getTimestamp(),
            'nbf'       => $issuedAt->getTimestamp(),
            'exp'       => $expiresAt->getTimestamp(),
            'jti'       => $tokenId,
            'wli'       => 1,
            'client_id' => $clientId,
        ];

        if ($scopes !== []) {
            $payload['scope'] = implode(separator: ' ', array: $scopes);
        }

        if ($audience !== null && trim(string: $audience) !== '') {
            $payload['aud'] = trim(string: $audience);
        }

        if ($senderConstraint !== null) {
            $payload['cnf_typ']        = $senderConstraint->type->value;
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
        string|null                  $expectedAudience = null,
        string|null                  $expectedIssuer = null
    ) : ResolvedWorkloadToken|null
    {
        try {
            $claims = $this->codec->decode(token: $token);

            if ($claims === null) {
                return null;
            }

            $expiresAt                  = $claims['exp'] ?? null;
            $issuedAt                   = $claims['iat'] ?? null;
            $notBefore                  = $claims['nbf'] ?? null;
            $subject                    = $claims['sub'] ?? null;
            $tokenId                    = $claims['jti'] ?? null;
            $clientId                   = $claims['client_id'] ?? null;
            $issuer                     = $claims['iss'] ?? null;
            $audience                   = $claims['aud'] ?? null;
            $workloadIdentity           = ($claims['wli'] ?? 0) === 1;
            $scopeClaim                 = $claims['scope'] ?? null;
            $senderConstraintType       = $claims['cnf_typ'] ?? null;
            $senderConstraintThumbprint = $claims['cnf_thumbprint'] ?? null;
            $scopes                     = [];
            $senderConstraint           = null;

            if (! $workloadIdentity
                || ! is_int(value: $expiresAt)
                || ! is_int(value: $issuedAt)
                || ! is_int(value: $notBefore)
                || ! is_string(value: $subject)
                || trim(string: $subject) === ''
                || ! is_string(value: $tokenId)
                || ! is_string(value: $clientId)
                || ! is_string(value: $issuer) || $issuer !== ($expectedIssuer ?? $this->issuer)) {
                return null;
            }

            if ($audience !== null && ! is_string(value: $audience)) {
                return null;
            }

            if ($expectedAudience !== null && $audience !== $expectedAudience) {
                return null;
            }

            $nowTimestamp = $this->clock->now()->getTimestamp();

            if ($issuedAt > ($nowTimestamp + $this->leeway) || $notBefore > ($nowTimestamp + $this->leeway) || $expiresAt <= ($nowTimestamp - $this->leeway) || $this->revocationStore?->isRevoked(tokenId: $tokenId, moment: $this->clock->now()) === true) {
                return null;
            }

            if (is_string(value: $scopeClaim) && $scopeClaim !== '') {
                $scopes = array_values(array: array_filter(
                                           array   : explode(separator: ' ', string: $scopeClaim),
                                           callback: static fn (string $scope) : bool => $scope !== ''
                ));
            }

            if ($senderConstraintType !== null || $senderConstraintThumbprint !== null) {
                if (! is_string(value: $senderConstraintType) || ! is_string(value: $senderConstraintThumbprint)) {
                    return null;
                }

                $senderConstraint = new OAuthSenderConstraint(
                    type      : OAuthSenderConstraintType::from(value: $senderConstraintType),
                    thumbprint: $senderConstraintThumbprint
                );
            }

            return new ResolvedWorkloadToken(
                subject         : $subject,
                clientId        : $clientId,
                tokenId         : $tokenId,
                expiresAt       : new DateTimeImmutable(datetime: "@{$expiresAt}"),
                scopes          : $scopes,
                audience        : $audience,
                issuer          : $issuer,
                senderConstraint: $senderConstraint
            );
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param list<string> $scopes
     *
     * @throws DateMalformedStringException
     */
    public function issueRefreshToken(
        User                       $user,
        DateTimeImmutable|null     $mfaVerifiedAt = null,
        bool                       $phishingResistant = false,
        string|null                $clientId = null,
        array                      $scopes = [],
        OAuthSenderConstraint|null $senderConstraint = null
    ) : IssuedRefreshToken|null
    {
        if ($this->refreshTokenStore === null) {
            return null;
        }

        return $this->refreshTokenStore->issue(
            userId           : $user->getId(),
            expiresAt        : $this->clock->now()->modify(modifier: "+{$this->refreshTokenExpiry} seconds"),
            mfaVerifiedAt    : $mfaVerifiedAt,
            phishingResistant: $phishingResistant,
            clientId         : $clientId,
            scopes           : $scopes,
            senderConstraint : $senderConstraint
        );
    }

    /**
     * @param list<string> $scopes
     *
     * @throws RandomException
     * @throws DateMalformedStringException
     */
    public function issue(
        User                              $user,
        DateTimeImmutable|null            $mfaVerifiedAt = null,
        bool                              $phishingResistant = false,
        string|null                       $clientId = null,
        array                             $scopes = [],
        #[SensitiveParameter] string|null $refreshTokenFamilyId = null,
        OAuthSenderConstraint|null        $senderConstraint = null
    ) : IssuedToken
    {
        if (! $user->isActive()) {
            throw new InvalidArgumentException(message: 'Inactive users cannot be authenticated.');
        }

        $issuedAt  = $this->clock->now();
        $expiresAt = $issuedAt->modify(modifier: "+{$this->tokenExpiry} seconds");
        $tokenId   = bin2hex(string: random_bytes(length: 16));
        $payload   = [
            'iss' => $this->issuer,
            'sub' => $user->getId()->value,
            'iat' => $issuedAt->getTimestamp(),
            'nbf' => $issuedAt->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
            'jti' => $tokenId,
        ];

        if ($refreshTokenFamilyId !== null) {
            $payload['fid'] = $refreshTokenFamilyId;
        }

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
            $payload['scope'] = implode(separator: ' ', array: $scopes);
        }

        if ($senderConstraint !== null) {
            $payload['cnf_typ']        = $senderConstraint->type->value;
            $payload['cnf_thumbprint'] = $senderConstraint->thumbprint;
        }

        return new IssuedToken(
            token    : $this->codec->encode(claims: $payload),
            tokenId  : $tokenId,
            expiresAt: $expiresAt
        );
    }

    public function revoke(#[SensitiveParameter] string $tokenId, DateTimeImmutable $expiresAt) : void
    {
        $this->revocationStore?->revoke(tokenId: $tokenId, expiresAt: $expiresAt);
    }
}
