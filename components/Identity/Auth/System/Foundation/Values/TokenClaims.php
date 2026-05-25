<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Foundation\Values;

use Avax\Components\Identity\Auth\System\Foundation\Ids\TenantId;
use Avax\Components\Identity\Auth\System\Foundation\Ids\TokenId;
use Avax\Components\Identity\Auth\System\Foundation\Ids\UserId;
use DateTimeImmutable;

/**
 * TokenClaims — value object representing the claims inside an access token.
 *
 * Adapted from the enterprise reference package.
 * Immutable; provides serialization to/from payload arrays for signing codecs.
 */
final readonly class TokenClaims
{
    /** @param list<string> $scopes */
    public function __construct(
        private TokenId $tokenId,
        private UserId $userId,
        private DateTimeImmutable $issuedAt,
        private DateTimeImmutable $expiresAt,
        private TenantId|null $tenantId = null,
        private array $scopes = [],
    ) {}

    public function tokenId(): TokenId
    {
        return $this->tokenId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function tenantId(): TenantId|null
    {
        return $this->tenantId;
    }

    /** @return list<string> */
    public function scopes(): array
    {
        return $this->scopes;
    }

    public function issuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpiredAt(DateTimeImmutable $time): bool
    {
        return $this->expiresAt <= $time;
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'jti' => (string) $this->tokenId,
            'sub' => (string) $this->userId,
            'iat' => $this->issuedAt->getTimestamp(),
            'exp' => $this->expiresAt->getTimestamp(),
            'tenant' => $this->tenantId !== null ? (string) $this->tenantId : null,
            'scopes' => $this->scopes,
        ];
    }

    /** @param array<string, mixed> $payload */
    public static function fromPayload(array $payload): self
    {
        $tenant = isset($payload['tenant']) && is_string($payload['tenant']) && $payload['tenant'] !== ''
            ? new TenantId($payload['tenant'])
            : null;

        $scopes = [];
        if (isset($payload['scopes']) && is_array($payload['scopes'])) {
            foreach ($payload['scopes'] as $scope) {
                if (is_string($scope)) {
                    $scopes[] = $scope;
                }
            }
        }

        return new self(
            tokenId: new TokenId((string) $payload['jti']),
            userId: new UserId((string) $payload['sub']),
            issuedAt: (new DateTimeImmutable())->setTimestamp((int) $payload['iat']),
            expiresAt: (new DateTimeImmutable())->setTimestamp((int) $payload['exp']),
            tenantId: $tenant,
            scopes: $scopes,
        );
    }
}
