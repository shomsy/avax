<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Tokens\Runtime;

use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Opaque refresh token pair returned to clients.
 */
final readonly class IssuedRefreshToken
{
    public OAuthSenderConstraint|null $senderConstraint;
    /** @var list<string> */
    public array                      $scopes;
    public string|null                $clientId;
    public bool                       $phishingResistant;
    public DateTimeImmutable|null     $mfaVerifiedAt;
    public DateTimeImmutable          $expiresAt;
    public UserId                     $userId;
    public string                     $familyId;
    public string                     $tokenId;
    public string                     $token;

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        #[SensitiveParameter] string $token,
        #[SensitiveParameter] string $tokenId,
        string                       $familyId,
        UserId                       $userId,
        DateTimeImmutable            $expiresAt,
        DateTimeImmutable|null       $mfaVerifiedAt = null,
        bool|null                    $phishingResistant = null,
        string|null                  $clientId = null,
        array|null                   $scopes = null,
        OAuthSenderConstraint|null   $senderConstraint = null
    )
    {
        $phishingResistant       ??= false;
        $scopes                  ??= [];
        $this->token             = $token;
        $this->tokenId           = $tokenId;
        $this->familyId          = $familyId;
        $this->userId            = $userId;
        $this->expiresAt         = $expiresAt;
        $this->mfaVerifiedAt     = $mfaVerifiedAt;
        $this->phishingResistant = $phishingResistant;
        $this->clientId          = $clientId;
        $this->scopes            = $scopes;
        $this->senderConstraint  = $senderConstraint;
    }
}
