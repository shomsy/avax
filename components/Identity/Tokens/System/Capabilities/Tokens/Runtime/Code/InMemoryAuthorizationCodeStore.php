<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Code;

use DateTimeImmutable;
use Random\RandomException;
use SensitiveParameter;

final class InMemoryAuthorizationCodeStore implements AuthorizationCodeStoreInterface
{
    /** @var array<string, AuthorizationCodeRecord> */
    private array $codes = [];

    /**
     * @param  list<string>  $scopes
     *
     * @throws RandomException
     */
    public function create(
        string $subject,
        DateTimeImmutable $expiresAt,
        ?string $clientId = null,
        array $scopes = [],
        ?string $redirectUri = null,
        ?string $state = null,
    ): AuthorizationCodeRecord {
        $code = bin2hex(string: random_bytes(length: 32));
        $authorizationCodeRecord = new AuthorizationCodeRecord(
            code       : $code,
            subject    : $subject,
            expiresAt  : $expiresAt,
            clientId   : $clientId,
            scopes     : array_values(array: $scopes),
            redirectUri: $redirectUri,
            state      : $state,
        );

        $this->codes[$code] = $authorizationCodeRecord;

        return $authorizationCodeRecord;
    }

    public function consume(#[SensitiveParameter] string $code, DateTimeImmutable $moment): ?AuthorizationCodeRecord
    {
        $record = $this->codes[$code] ?? null;
        unset($this->codes[$code]);

        if (! $record instanceof AuthorizationCodeRecord || $record->isExpired(moment: $moment)) {
            return null;
        }

        return $record;
    }
}
