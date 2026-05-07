<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;
use Random\RandomException;
use SensitiveParameter;

/**
 * In-memory authorization code storage.
 */
final class InMemoryAuthorizationCodeStore implements AuthorizationCodeStoreInterface, PruneExpiredAuthorizationCodesInterface
{
    /** @var array<string, string> */
    private array $hashToCodeId = [];

    /** @var array<string, AuthorizationCodeRecord> */
    private array $records = [];

    /**
     * @throws RandomException
     */
    public function issue(
        UserId             $userId,
        string             $clientId,
        string             $redirectUri,
        array              $scopes,
        DateTimeImmutable  $expiresAt,
        ?string            $state = null,
        ?string            $nonce = null,
        #[SensitiveParameter]
        ?string            $codeChallenge = null,
        #[SensitiveParameter]
        ?PkceMethod        $pkceMethod = null,
        ?DateTimeImmutable $mfaVerifiedAt = null,
        bool               $phishingResistant = false,
    ) : IssuedAuthorizationCode
    {
        $plainCode = bin2hex(string: random_bytes(length: 32));
        $codeId    = 'code_' . bin2hex(string: random_bytes(length: 12));

        $this->records[$codeId]                                 = new AuthorizationCodeRecord(
            codeId             : $codeId,
            clientId           : $clientId,
            userId             : $userId,
            redirectUri        : $redirectUri,
            scopes             : $scopes,
            expiresAt          : $expiresAt,
            nonce              : $nonce,
            codeChallenge      : $codeChallenge,
            codeChallengeMethod: $pkceMethod,
            mfaVerifiedAt      : $mfaVerifiedAt,
            phishingResistant  : $phishingResistant,
        );
        $this->hashToCodeId[$this->hash(plainCode: $plainCode)] = $codeId;

        return new IssuedAuthorizationCode(
            code     : $plainCode,
            codeId   : $codeId,
            expiresAt: $expiresAt,
            state    : $state,
        );
    }

    private function hash(#[SensitiveParameter] string $plainCode) : string
    {
        return hash(algo: 'sha256', data: $plainCode);
    }

    public function find(#[SensitiveParameter] string $plainCode) : ?AuthorizationCodeRecord
    {
        $codeId = $this->hashToCodeId[$this->hash(plainCode: $plainCode)] ?? null;

        if ($codeId === null) {
            return null;
        }

        return $this->records[$codeId] ?? null;
    }

    public function markUsed(#[SensitiveParameter] string $codeId, DateTimeImmutable $usedAt) : void
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
            nonce              : $record->nonce,
            codeChallenge      : $record->codeChallenge,
            codeChallengeMethod: $record->codeChallengeMethod,
            usedAt             : $usedAt,
            mfaVerifiedAt      : $record->mfaVerifiedAt,
            phishingResistant  : $record->phishingResistant,
        );
    }

    public function pruneExpired(DateTimeImmutable $now) : int
    {
        $removed = 0;

        foreach ($this->records as $codeId => $record) {
            if (! $record->wasUsed() && ! $record->isExpiredAt(moment: $now)) {
                continue;
            }

            unset($this->records[$codeId]);
            $removed++;
        }

        if ($removed === 0) {
            return 0;
        }

        $this->hashToCodeId = array_filter(
            array   : $this->hashToCodeId,
            callback: fn (#[SensitiveParameter] string $codeId) : bool => isset($this->records[$codeId]),
        );

        return $removed;
    }
}
