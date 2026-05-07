<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\System\Capabilities\Tokens\Runtime\Code;

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
        public ?string           $clientId = null,
        public array             $scopes = [],
        public ?string           $redirectUri = null,
        public ?string           $state = null,
    ) {}

    public function isExpired(DateTimeImmutable $moment) : bool
    {
        return $this->expiresAt <= $moment;
    }
}
