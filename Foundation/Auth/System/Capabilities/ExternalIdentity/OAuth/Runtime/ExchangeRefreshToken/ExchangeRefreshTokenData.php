<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeRefreshToken;

use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use SensitiveParameter;

final readonly class ExchangeRefreshTokenData
{
    public OAuthSenderConstraint|null $senderConstraint;
    public string|null                $userAgent;
    public string|null                $ipAddress;
    public string|null                $clientSecret;
    public string                     $refreshToken;
    public string                     $clientId;

    public function __construct(
        string                            $clientId,
        #[SensitiveParameter] string      $refreshToken,
        #[SensitiveParameter] string|null $clientSecret = null,
        #[SensitiveParameter] string|null $ipAddress = null,
        string|null                       $userAgent = null,
        OAuthSenderConstraint|null        $senderConstraint = null
    )
    {
        $this->clientId         = $clientId;
        $this->refreshToken     = $refreshToken;
        $this->clientSecret     = $clientSecret;
        $this->ipAddress        = $ipAddress;
        $this->userAgent        = $userAgent;
        $this->senderConstraint = $senderConstraint;
    }
}
