<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\PushAuthorizationRequest;

use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\PkceMethod;
use SensitiveParameter;

final readonly class PushAuthorizationRequestData
{
    public string|null     $userAgent;
    public string|null     $ipAddress;
    public PkceMethod|null $codeChallengeMethod;
    public string|null     $codeChallenge;
    public string|null     $clientSecret;
    public string|null     $requestObjectJwt;
    public string|null     $nonce;
    public string|null     $state;
    /** @var list<string> */
    public array           $scopes;
    public string          $redirectUri;
    public string          $clientId;

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        string                                $clientId,
        string                                $redirectUri,
        array|null                            $scopes = null,
        string|null                           $state = null,
        string|null                           $nonce = null,
        #[SensitiveParameter] string|null     $requestObjectJwt = null,
        #[SensitiveParameter] string|null     $clientSecret = null,
        #[SensitiveParameter] string|null     $codeChallenge = null,
        #[SensitiveParameter] PkceMethod|null $codeChallengeMethod = null,
        #[SensitiveParameter] string|null     $ipAddress = null,
        string|null                           $userAgent = null
    )
    {
        $scopes                    ??= [];
        $this->clientId            = $clientId;
        $this->redirectUri         = $redirectUri;
        $this->scopes              = $scopes;
        $this->state               = $state;
        $this->nonce               = $nonce;
        $this->requestObjectJwt    = $requestObjectJwt;
        $this->clientSecret        = $clientSecret;
        $this->codeChallenge       = $codeChallenge;
        $this->codeChallengeMethod = $codeChallengeMethod;
        $this->ipAddress           = $ipAddress;
        $this->userAgent           = $userAgent;
    }
}
