<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\ValidateRequestObject;

use Avax\Auth\System\Capability\OAuth\PkceMethod;
use SensitiveParameter;

final readonly class ValidatedRequestObject
{
    public PkceMethod|null $codeChallengeMethod;
    public string|null     $codeChallenge;
    public string|null     $nonce;
    public string|null     $state;
    /** @var list<string> */
    public array           $scopes;
    public string          $redirectUri;
    public string          $clientId;
    public string          $requestUri;

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        #[SensitiveParameter] string          $requestUri,
        string                                $clientId,
        string                                $redirectUri,
        array|null                            $scopes = null,
        string|null                           $state = null,
        string|null                           $nonce = null,
        #[SensitiveParameter] string|null     $codeChallenge = null,
        #[SensitiveParameter] PkceMethod|null $codeChallengeMethod = null
    )
    {
        $scopes                    ??= [];
        $this->requestUri          = $requestUri;
        $this->clientId            = $clientId;
        $this->redirectUri         = $redirectUri;
        $this->scopes              = $scopes;
        $this->state               = $state;
        $this->nonce               = $nonce;
        $this->codeChallenge       = $codeChallenge;
        $this->codeChallengeMethod = $codeChallengeMethod;
    }
}
