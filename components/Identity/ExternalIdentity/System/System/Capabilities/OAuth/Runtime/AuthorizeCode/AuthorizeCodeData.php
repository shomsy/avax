<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Runtime\AuthorizeCode;

use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Elements\PkceMethod;
use SensitiveParameter;

final readonly class AuthorizeCodeData
{
    /** @var list<string> */
    public array $scopes;

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public string      $clientId,
        public string      $redirectUri,
        ?array             $scopes = null,
        public ?string     $state = null,
        public ?string     $nonce = null,
        public ?string     $requestUri = null,
        #[SensitiveParameter]
        public ?string     $codeChallenge = null,
        #[SensitiveParameter]
        public ?PkceMethod $codeChallengeMethod = null,
        #[SensitiveParameter]
        public ?string     $ipAddress = null,
        public ?string     $userAgent = null,
    )
    {
        $scopes       ??= [];
        $this->scopes = $scopes;
    }
}
