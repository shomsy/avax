<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * One-time authorization code returned to the client redirect layer.
 */
final readonly class IssuedAuthorizationCode
{
    public function __construct(
        #[SensitiveParameter]
        public string            $code,
        #[SensitiveParameter]
        public string            $codeId,
        public DateTimeImmutable $expiresAt,
        public ?string           $state = null,
    ) {}
}
