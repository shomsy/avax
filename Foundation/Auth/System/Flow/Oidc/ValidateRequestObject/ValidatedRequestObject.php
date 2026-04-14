<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\ValidateRequestObject;

use Avax\Auth\System\Capability\OAuth\PkceMethod;
use SensitiveParameter;

final readonly class ValidatedRequestObject
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        #[SensitiveParameter] public string $requestUri,
        public string $clientId,
        public string $redirectUri,
        public array $scopes = [],
        public string|null $state = null,
        public string|null $nonce = null,
        #[SensitiveParameter] public string|null $codeChallenge = null,
        #[SensitiveParameter] public PkceMethod|null $codeChallengeMethod = null
    ) {}
}
