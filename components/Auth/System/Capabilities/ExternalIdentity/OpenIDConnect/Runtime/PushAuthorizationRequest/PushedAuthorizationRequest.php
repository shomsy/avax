<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\PushAuthorizationRequest;

use components\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\PkceMethod;
use DateTimeImmutable;
use SensitiveParameter;

final readonly class PushedAuthorizationRequest
{
    /** @var list<string> */
    public array $scopes;

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        #[SensitiveParameter] public string          $requestUri,
        public DateTimeImmutable                     $expiresAt,
        public string                                $clientId,
        public string                                $redirectUri,
        array|null                                   $scopes = null,
        public string|null                           $state = null,
        public string|null                           $nonce = null,
        #[SensitiveParameter] public string|null     $codeChallenge = null,
        #[SensitiveParameter] public PkceMethod|null $codeChallengeMethod = null
    )
    {
        $scopes       ??= [];
        $this->scopes = $scopes;
    }
}
