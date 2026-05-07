<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraint;
use DateTimeImmutable;
use SensitiveParameter;

final readonly class ResolvedWorkloadToken
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public string                 $subject,
        public string                 $clientId,
        #[SensitiveParameter]
        public string                 $tokenId,
        public DateTimeImmutable      $expiresAt,
        public array                  $scopes = [],
        public ?string                $audience = null,
        public ?string                $issuer = null,
        public ?OAuthSenderConstraint $senderConstraint = null,
    ) {}
}
