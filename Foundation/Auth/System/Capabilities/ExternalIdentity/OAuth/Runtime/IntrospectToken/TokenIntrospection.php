<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\OAuth\IntrospectToken;

use Avax\Auth\System\Capabilities\OAuth\SenderConstraint\OAuthSenderConstraint;
use DateTimeImmutable;

final readonly class TokenIntrospection
{
    public bool                       $workloadIdentity;
    public string|null                $issuer;
    public string|null                $audience;
    public string|null                $subject;
    public OAuthSenderConstraint|null $senderConstraint;
    public bool                       $phishingResistant;
    public DateTimeImmutable|null     $mfaVerifiedAt;
    public DateTimeImmutable|null     $expiresAt;
    /** @var list<string> */
    public array                      $scopes;
    public int|null                   $userId;
    public string|null                $clientId;
    public bool                       $active;

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        bool                       $active,
        string|null                $clientId = null,
        int|null                   $userId = null,
        array|null                 $scopes = null,
        DateTimeImmutable|null     $expiresAt = null,
        DateTimeImmutable|null     $mfaVerifiedAt = null,
        bool|null                  $phishingResistant = null,
        OAuthSenderConstraint|null $senderConstraint = null,
        string|null                $subject = null,
        string|null                $audience = null,
        string|null                $issuer = null,
        bool                       $workloadIdentity = false
    )
    {
        $scopes                  ??= [];
        $phishingResistant       ??= false;
        $this->active            = $active;
        $this->clientId          = $clientId;
        $this->userId            = $userId;
        $this->scopes            = $scopes;
        $this->expiresAt         = $expiresAt;
        $this->mfaVerifiedAt     = $mfaVerifiedAt;
        $this->phishingResistant = $phishingResistant;
        $this->senderConstraint  = $senderConstraint;
        $this->subject           = $subject;
        $this->audience          = $audience;
        $this->issuer            = $issuer;
        $this->workloadIdentity  = $workloadIdentity;
    }

    public static function inactive() : self
    {
        return new self(active: false);
    }

    public function scopeString() : string
    {
        return implode(' ', $this->scopes);
    }
}
