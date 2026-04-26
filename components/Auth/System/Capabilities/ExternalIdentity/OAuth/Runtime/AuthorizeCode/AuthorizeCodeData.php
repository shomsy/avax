<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\AuthorizeCode;

use components\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\PkceMethod;
use SensitiveParameter;

final readonly class AuthorizeCodeData
{
    /** @var list<string> */
    public array $scopes;

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public string                                $clientId,
        public string                                $redirectUri,
        array|null                                   $scopes = null,
        public string|null                           $state = null,
        public string|null                           $nonce = null,
        public string|null                           $requestUri = null,
        #[SensitiveParameter] public string|null     $codeChallenge = null,
        #[SensitiveParameter] public PkceMethod|null $codeChallengeMethod = null,
        #[SensitiveParameter] public string|null     $ipAddress = null,
        public string|null                           $userAgent = null
    )
    {
        $scopes       ??= [];
        $this->scopes = $scopes;
    }
}
