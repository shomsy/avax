<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Tokens\Runtime;

use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capabilities\Identity\User\User;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Successfully verified access token state.
 */
final readonly class ResolvedToken
{
    public string|null                $familyId;
    public OAuthSenderConstraint|null $senderConstraint;
    /** @var list<string> */
    public array                      $scopes;
    public string|null                $clientId;
    public bool                       $phishingResistant;
    public DateTimeImmutable|null     $mfaVerifiedAt;
    public DateTimeImmutable          $expiresAt;
    public string                     $tokenId;
    public User                       $user;

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        User                         $user,
        #[SensitiveParameter] string $tokenId,
        DateTimeImmutable            $expiresAt,
        DateTimeImmutable|null       $mfaVerifiedAt = null,
        bool|null                    $phishingResistant = null,
        string|null                  $clientId = null,
        array|null                   $scopes = null,
        OAuthSenderConstraint|null   $senderConstraint = null,
        string|null                  $familyId = null
    )
    {
        $phishingResistant       ??= false;
        $scopes                  ??= [];
        $this->user              = $user;
        $this->tokenId           = $tokenId;
        $this->expiresAt         = $expiresAt;
        $this->mfaVerifiedAt     = $mfaVerifiedAt;
        $this->phishingResistant = $phishingResistant;
        $this->clientId          = $clientId;
        $this->scopes            = $scopes;
        $this->senderConstraint  = $senderConstraint;
        $this->familyId          = $familyId;
    }
}
