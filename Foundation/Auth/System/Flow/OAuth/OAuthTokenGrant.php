<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * OAuth token endpoint result.
 */
final readonly class OAuthTokenGrant
{
    public bool                       $workloadIdentity;
    public string|null                $audience;
    public string|null                $subject;
    public OAuthSenderConstraint|null $senderConstraint;
    public string                     $tokenType;
    /** @var list<string> */
    public array                      $scopes;
    public int|null                   $userId;
    public string                     $clientId;
    public string|null                $idToken;
    public string|null                $refreshToken;
    public DateTimeImmutable          $accessTokenExpiresAt;
    public string                     $accessToken;

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        #[SensitiveParameter] string            $accessToken,
        #[SensitiveParameter] DateTimeImmutable $accessTokenExpiresAt,
        #[SensitiveParameter] string|null       $refreshToken,
        #[SensitiveParameter] string|null       $idToken,
        string                                  $clientId,
        int|null                                $userId,
        array|null                              $scopes = null,
        #[SensitiveParameter] string|null       $tokenType = null,
        OAuthSenderConstraint|null              $senderConstraint = null,
        string|null                             $subject = null,
        string|null                             $audience = null,
        bool                                    $workloadIdentity = false
    )
    {
        $scopes                     ??= [];
        $tokenType                  ??= 'Bearer';
        $this->accessToken          = $accessToken;
        $this->accessTokenExpiresAt = $accessTokenExpiresAt;
        $this->refreshToken         = $refreshToken;
        $this->idToken              = $idToken;
        $this->clientId             = $clientId;
        $this->userId               = $userId;
        $this->scopes               = $scopes;
        $this->tokenType            = $tokenType;
        $this->senderConstraint     = $senderConstraint;
        $this->subject              = $subject;
        $this->audience             = $audience;
        $this->workloadIdentity     = $workloadIdentity;
    }

    public function expiresIn(DateTimeImmutable $moment) : int
    {
        return max(0, $this->accessTokenExpiresAt->getTimestamp() - $moment->getTimestamp());
    }

    public function scopeString() : string
    {
        return implode(' ', $this->scopes);
    }
}
