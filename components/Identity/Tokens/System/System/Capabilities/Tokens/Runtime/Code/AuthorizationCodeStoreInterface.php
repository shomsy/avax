<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\System\Capabilities\Tokens\Runtime\Code;

use DateTimeImmutable;
use SensitiveParameter;

interface AuthorizationCodeStoreInterface
{
    /**
     * @param list<string> $scopes
     */
    public function create(
        string            $subject,
        DateTimeImmutable $expiresAt,
        ?string           $clientId = null,
        array             $scopes = [],
        ?string           $redirectUri = null,
        ?string           $state = null,
    ) : AuthorizationCodeRecord;

    public function consume(#[SensitiveParameter] string $code, DateTimeImmutable $moment) : ?AuthorizationCodeRecord;
}
