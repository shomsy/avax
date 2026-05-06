<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\PushAuthorizationRequest;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\PkceMethod;
use DateTimeImmutable;
use SensitiveParameter;

final readonly class PushedAuthorizationRequest
{
    /** @var list<string> */
    public array $scopes;

    /**
     * @param  list<string>  $scopes
     */
    public function __construct(
        #[SensitiveParameter]
        public string $requestUri,
        public DateTimeImmutable $expiresAt,
        public string $clientId,
        public string $redirectUri,
        ?array $scopes = null,
        public ?string $state = null,
        public ?string $nonce = null,
        #[SensitiveParameter]
        public ?string $codeChallenge = null,
        #[SensitiveParameter]
        public ?PkceMethod $codeChallengeMethod = null,
    ) {
        $scopes ??= [];
        $this->scopes = $scopes;
    }
}
