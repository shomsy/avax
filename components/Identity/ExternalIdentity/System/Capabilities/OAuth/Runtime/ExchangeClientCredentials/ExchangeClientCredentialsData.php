<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeClientCredentials;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraint;
use SensitiveParameter;

final readonly class ExchangeClientCredentialsData
{
    /** @var list<string> */
    public array $scopes;

    /**
     * @param  list<string>  $scopes
     */
    public function __construct(
        public string $clientId,
        #[SensitiveParameter]
        public ?string $clientSecret = null,
        ?array $scopes = null,
        public ?string $audience = null,
        #[SensitiveParameter]
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
        public ?OAuthSenderConstraint $senderConstraint = null,
    ) {
        $scopes ??= [];
        $this->scopes = $scopes;
    }
}
