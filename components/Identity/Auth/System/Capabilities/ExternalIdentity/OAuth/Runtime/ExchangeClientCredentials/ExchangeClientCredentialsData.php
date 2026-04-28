<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeClientCredentials;

use Avax\Components\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use SensitiveParameter;

final readonly class ExchangeClientCredentialsData
{
    /** @var list<string> */
    public array $scopes;

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public string                            $clientId,
        #[SensitiveParameter] public string|null $clientSecret = null,
        array|null                               $scopes = null,
        public string|null                       $audience = null,
        #[SensitiveParameter] public string|null $ipAddress = null,
        public string|null                       $userAgent = null,
        public OAuthSenderConstraint|null        $senderConstraint = null
    )
    {
        $scopes       ??= [];
        $this->scopes = $scopes;
    }
}
