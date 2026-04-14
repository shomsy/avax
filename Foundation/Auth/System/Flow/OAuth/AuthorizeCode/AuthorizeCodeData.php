<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\AuthorizeCode;

use Avax\Auth\System\Capability\OAuth\PkceMethod;
use SensitiveParameter;

final readonly class AuthorizeCodeData
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public string                                $clientId,
        public string                                $redirectUri,
        public array                                 $scopes = [],
        public string|null                           $state = null,
        public string|null                           $nonce = null,
        public string|null                           $requestUri = null,
        #[SensitiveParameter] public string|null     $codeChallenge = null,
        #[SensitiveParameter] public PkceMethod|null $codeChallengeMethod = null,
        #[SensitiveParameter] public string|null     $ipAddress = null,
        public string|null                           $userAgent = null
    ) {}
}
