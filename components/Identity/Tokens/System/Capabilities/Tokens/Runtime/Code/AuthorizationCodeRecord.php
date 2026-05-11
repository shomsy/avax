<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Code;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class AuthorizationCodeRecord
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        #[SensitiveParameter]
        public string            $code,
        public string            $subject,
        public DateTimeImmutable $expiresAt,
        public string|null $clientId = null,
        public array             $scopes = [],
        public string|null $redirectUri = null,
        public string|null $state = null,
    ) {}

    public function isExpired(DateTimeImmutable $moment) : bool
    {
        return $this->expiresAt <= $moment;
    }
}
