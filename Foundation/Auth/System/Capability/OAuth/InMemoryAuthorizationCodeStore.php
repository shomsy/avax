<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth;

use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;

/**
 * In-memory authorization code storage.
 */
final class InMemoryAuthorizationCodeStore implements AuthorizationCodeStoreInterface, PruneExpiredAuthorizationCodesInterface
{
    /** @var array<string, string> */
    private array $hashToCodeId = [];

    /** @var array<string, AuthorizationCodeRecord> */
    private array $records = [];

    public function issue(
        UserId $userId,
        string $clientId,
        string $redirectUri,
        array $scopes,
        DateTimeImmutable $expiresAt,
        string|null $state = null,
        string|null $codeChallenge = null,
        PkceMethod|null $codeChallengeMethod = null,
        DateTimeImmutable|null $mfaVerifiedAt = null
    ) : IssuedAuthorizationCode
    {
        $plainCode = bin2hex(random_bytes(32));
        $codeId    = 'code_' . bin2hex(random_bytes(12));

        $this->records[$codeId] = new AuthorizationCodeRecord(
            codeId             : $codeId,
            clientId           : $clientId,
            userId             : $userId,
            redirectUri        : $redirectUri,
            scopes             : array_values($scopes),
            expiresAt          : $expiresAt,
            codeChallenge      : $codeChallenge,
            codeChallengeMethod: $codeChallengeMethod,
            mfaVerifiedAt      : $mfaVerifiedAt
        );
        $this->hashToCodeId[$this->hash($plainCode)] = $codeId;

        return new IssuedAuthorizationCode(
            code     : $plainCode,
            codeId   : $codeId,
            expiresAt: $expiresAt,
            state    : $state
        );
    }

    public function find(string $plainCode) : AuthorizationCodeRecord|null
    {
        $codeId = $this->hashToCodeId[$this->hash($plainCode)] ?? null;

        if ($codeId === null) {
            return null;
        }

        return $this->records[$codeId] ?? null;
    }

    public function markUsed(string $codeId, DateTimeImmutable $usedAt) : void
    {
        $record = $this->records[$codeId] ?? null;

        if ($record === null) {
            return;
        }

        $this->records[$codeId] = new AuthorizationCodeRecord(
            codeId             : $record->codeId,
            clientId           : $record->clientId,
            userId             : $record->userId,
            redirectUri        : $record->redirectUri,
            scopes             : $record->scopes,
            expiresAt          : $record->expiresAt,
            codeChallenge      : $record->codeChallenge,
            codeChallengeMethod: $record->codeChallengeMethod,
            usedAt             : $usedAt,
            mfaVerifiedAt      : $record->mfaVerifiedAt
        );
    }

    public function pruneExpired(DateTimeImmutable $now) : int
    {
        $removed = 0;

        foreach ($this->records as $codeId => $record) {
            if (! $record->wasUsed() && ! $record->isExpiredAt($now)) {
                continue;
            }

            unset($this->records[$codeId]);
            $removed++;
        }

        if ($removed === 0) {
            return 0;
        }

        $this->hashToCodeId = array_filter(
            $this->hashToCodeId,
            fn (string $codeId) : bool => isset($this->records[$codeId])
        );

        return $removed;
    }

    private function hash(string $plainCode) : string
    {
        return hash('sha256', $plainCode);
    }
}
