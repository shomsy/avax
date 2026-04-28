<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Record;

use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use DateTimeImmutable;
use SensitiveParameter;

final readonly class ResolvedWorkloadToken
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public string                       $subject,
        public string                       $clientId,
        #[SensitiveParameter] public string $tokenId,
        public DateTimeImmutable            $expiresAt,
        public array                        $scopes = [],
        public string|null                  $audience = null,
        public string|null                  $issuer = null,
        public OAuthSenderConstraint|null   $senderConstraint = null
    ) {}
}
