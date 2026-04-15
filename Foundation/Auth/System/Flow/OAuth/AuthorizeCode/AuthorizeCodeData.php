<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\AuthorizeCode;

use Avax\Auth\System\Capability\OAuth\PkceMethod;
use SensitiveParameter;

final readonly class AuthorizeCodeData
{
    public string|null     $userAgent;
    public string|null     $ipAddress;
    public PkceMethod|null $codeChallengeMethod;
    public string|null     $codeChallenge;
    public string|null     $requestUri;
    public string|null     $nonce;
    public string|null     $state;
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
        string|null                           $requestUri = null,
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
        $this->requestUri          = $requestUri;
        $this->codeChallenge       = $codeChallenge;
        $this->codeChallengeMethod = $codeChallengeMethod;
        $this->ipAddress           = $ipAddress;
        $this->userAgent           = $userAgent;
    }
}
