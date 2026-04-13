<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\ExchangeClientCredentials;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use SensitiveParameter;

final readonly class ExchangeClientCredentialsData
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public string                             $clientId,
        #[SensitiveParameter] public string|null  $clientSecret = null,
        public array                              $scopes = [],
        public string|null                        $audience = null,
        #[\SensitiveParameter] public string|null $ipAddress = null,
        public string|null                        $userAgent = null,
        public OAuthSenderConstraint|null         $senderConstraint = null
    ) {}
}
