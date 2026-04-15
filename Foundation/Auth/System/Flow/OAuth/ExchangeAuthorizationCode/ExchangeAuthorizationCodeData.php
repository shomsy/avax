<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\ExchangeAuthorizationCode;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use SensitiveParameter;

final readonly class ExchangeAuthorizationCodeData
{
    public OAuthSenderConstraint|null $senderConstraint;
    public string|null                $userAgent;
    public string|null                $ipAddress;
    public string|null                $codeVerifier;
    public string|null                $clientSecret;
    public string                     $redirectUri;
    public string                     $code;
    public string                     $clientId;

    public function __construct(
        string                             $clientId,
        #[SensitiveParameter] string       $code,
        string                             $redirectUri,
        #[SensitiveParameter] string|null  $clientSecret = null,
        #[SensitiveParameter] string|null  $codeVerifier = null,
        #[\SensitiveParameter] string|null $ipAddress = null,
        string|null                        $userAgent = null,
        OAuthSenderConstraint|null         $senderConstraint = null
    )
    {
        $this->clientId         = $clientId;
        $this->code             = $code;
        $this->redirectUri      = $redirectUri;
        $this->clientSecret     = $clientSecret;
        $this->codeVerifier     = $codeVerifier;
        $this->ipAddress        = $ipAddress;
        $this->userAgent        = $userAgent;
        $this->senderConstraint = $senderConstraint;
    }
}
