<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\PushAuthorizationRequest;

use Avax\Auth\System\Capability\OAuth\PkceMethod;
use SensitiveParameter;

final readonly class PushAuthorizationRequestData
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public string $clientId,
        public string $redirectUri,
        public array $scopes = [],
        public string|null $state = null,
        public string|null $nonce = null,
        #[SensitiveParameter] public string|null $requestObjectJwt = null,
        #[SensitiveParameter] public string|null $clientSecret = null,
        #[SensitiveParameter] public string|null $codeChallenge = null,
        #[SensitiveParameter] public PkceMethod|null $codeChallengeMethod = null,
        #[SensitiveParameter] public string|null $ipAddress = null,
        public string|null $userAgent = null
    ) {}
}
