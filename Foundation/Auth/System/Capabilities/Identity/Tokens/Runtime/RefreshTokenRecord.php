<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Tokens\Runtime;

use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Stored refresh token state used for rotation and revocation checks.
 */
final readonly class RefreshTokenRecord
{
    public OAuthSenderConstraint|null $senderConstraint;
    /** @var list<string> */
    public array                      $scopes;
    public string|null                $clientId;
    public bool                       $phishingResistant;
    public DateTimeImmutable|null     $mfaVerifiedAt;
    public bool                       $revoked;
    public string|null                $replacementTokenId;
    public DateTimeImmutable          $expiresAt;
    public UserId                     $userId;
    public string                     $familyId;
    public string                     $tokenId;

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        #[SensitiveParameter] string      $tokenId,
        string                            $familyId,
        UserId                            $userId,
        DateTimeImmutable                 $expiresAt,
        #[SensitiveParameter] string|null $replacementTokenId = null,
        bool|null                         $revoked = null,
        DateTimeImmutable|null            $mfaVerifiedAt = null,
        bool|null                         $phishingResistant = null,
        string|null                       $clientId = null,
        array|null                        $scopes = null,
        OAuthSenderConstraint|null        $senderConstraint = null
    )
    {
        $revoked                  ??= false;
        $phishingResistant        ??= false;
        $scopes                   ??= [];
        $this->tokenId            = $tokenId;
        $this->familyId           = $familyId;
        $this->userId             = $userId;
        $this->expiresAt          = $expiresAt;
        $this->replacementTokenId = $replacementTokenId;
        $this->revoked            = $revoked;
        $this->mfaVerifiedAt      = $mfaVerifiedAt;
        $this->phishingResistant  = $phishingResistant;
        $this->clientId           = $clientId;
        $this->scopes             = $scopes;
        $this->senderConstraint   = $senderConstraint;
    }

    public function isExpiredAt(DateTimeImmutable $moment) : bool
    {
        return $this->expiresAt <= $moment;
    }

    public function wasRotated() : bool
    {
        return $this->replacementTokenId !== null;
    }
}
