<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Stored OAuth authorization code state.
 */
final readonly class AuthorizationCodeRecord
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        #[SensitiveParameter]
        public string $codeId,
        public string $clientId,
        public UserId $userId,
        public string $redirectUri,
        public array $scopes,
        public DateTimeImmutable $expiresAt,
        public ?string $nonce = null,
        #[SensitiveParameter]
        public ?string $codeChallenge = null,
        #[SensitiveParameter]
        public ?PkceMethod $codeChallengeMethod = null,
        public ?DateTimeImmutable $usedAt = null,
        public ?DateTimeImmutable $mfaVerifiedAt = null,
        public bool $phishingResistant = false,
    ) {}

    public function isExpiredAt(DateTimeImmutable $moment): bool
    {
        return $this->expiresAt <= $moment;
    }

    public function wasUsed(): bool
    {
        return $this->usedAt instanceof DateTimeImmutable;
    }
}
