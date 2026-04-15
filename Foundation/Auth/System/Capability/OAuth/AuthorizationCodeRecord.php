<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth;

use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Stored OAuth authorization code state.
 */
final readonly class AuthorizationCodeRecord
{
    public bool                   $phishingResistant;
    public DateTimeImmutable|null $mfaVerifiedAt;
    public DateTimeImmutable|null $usedAt;
    public PkceMethod|null        $codeChallengeMethod;
    public string|null            $codeChallenge;
    public string|null            $nonce;
    public DateTimeImmutable      $expiresAt;
    public array                  $scopes;
    public string                 $redirectUri;
    public UserId                 $userId;
    public string                 $clientId;
    public string                 $codeId;

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        #[SensitiveParameter] string          $codeId,
        string                                $clientId,
        UserId                                $userId,
        string                                $redirectUri,
        array                                 $scopes,
        DateTimeImmutable                     $expiresAt,
        string|null                           $nonce = null,
        #[SensitiveParameter] string|null     $codeChallenge = null,
        #[SensitiveParameter] PkceMethod|null $codeChallengeMethod = null,
        DateTimeImmutable|null                $usedAt = null,
        DateTimeImmutable|null                $mfaVerifiedAt = null,
        bool                                  $phishingResistant = false
    )
    {
        $this->codeId              = $codeId;
        $this->clientId            = $clientId;
        $this->userId              = $userId;
        $this->redirectUri         = $redirectUri;
        $this->scopes              = $scopes;
        $this->expiresAt           = $expiresAt;
        $this->nonce               = $nonce;
        $this->codeChallenge       = $codeChallenge;
        $this->codeChallengeMethod = $codeChallengeMethod;
        $this->usedAt              = $usedAt;
        $this->mfaVerifiedAt       = $mfaVerifiedAt;
        $this->phishingResistant   = $phishingResistant;
    }

    public function isExpiredAt(DateTimeImmutable $moment) : bool
    {
        return $this->expiresAt <= $moment;
    }

    public function wasUsed() : bool
    {
        return $this->usedAt !== null;
    }
}
