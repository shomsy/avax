<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Tokens;

use Avax\Components\Identity\Foundation\Values\TenantId;
use Avax\Components\Identity\Foundation\Values\TokenId;
use Avax\Components\Identity\Foundation\Values\UserId;
use DateTimeImmutable;

final readonly class TokenClaims
{
    public function __construct(
        private TokenId $tokenId,
        private UserId $userId,
        private DateTimeImmutable $issuedAt,
        private DateTimeImmutable $expiresAt,
        private TenantId|null $tenantId = null,
        /** @var list<string> */
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

    public function isExpiredAt(DateTimeImmutable $time): bool
    {
        return $this->expiresAt <= $time;
    }

    /** @return array<string, mixed> */
    public function toPayload(): array
    {
        return [
            'jti' => $this->tokenId->toString(),
            'sub' => $this->userId->toString(),
            'iat' => $this->issuedAt->getTimestamp(),
            'exp' => $this->expiresAt->getTimestamp(),
            'tenant' => $this->tenantId?->toString(),
            'scopes' => $this->scopes,
        ];
    }

    /** @param array<string, mixed> $payload */
    public static function fromPayload(array $payload): self
    {
        $tenant = isset($payload['tenant']) && is_string($payload['tenant']) && $payload['tenant'] !== ''
            ? TenantId::fromString($payload['tenant'])
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
            tokenId: TokenId::fromString((string) $payload['jti']),
            userId: UserId::fromString((string) $payload['sub']),
            issuedAt: (new DateTimeImmutable())->setTimestamp((int) $payload['iat']),
            expiresAt: (new DateTimeImmutable())->setTimestamp((int) $payload['exp']),
            tenantId: $tenant,
            scopes: $scopes,
        );
    }
}
