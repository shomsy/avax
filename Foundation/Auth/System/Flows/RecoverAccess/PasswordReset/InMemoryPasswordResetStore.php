<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\RecoverAccess\PasswordReset;

use Avax\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;
use Random\RandomException;
use SensitiveParameter;

/**
 * In-memory password reset storage for tests and demos.
 */
final class InMemoryPasswordResetStore implements PasswordResetStoreInterface, PruneExpiredPasswordResetsInterface
{
    /** @var array<string, array{user_id: int, expires_at: int}> */
    private array $records = [];

    /**
     * @throws RandomException
     */
    public function issue(UserId $userId, DateTimeImmutable $expiresAt) : PasswordResetChallenge
    {
        $token                                 = bin2hex(string: random_bytes(length: 32));
        $this->records[hash(algo: 'sha256', data: $token)] = [
            'user_id'    => $userId->value,
            'expires_at' => $expiresAt->getTimestamp(),
        ];

        return new PasswordResetChallenge(
            dispatched: true,
            token     : $token,
            expiresAt : $expiresAt
        );
    }

    public function consume(#[SensitiveParameter] string $token, DateTimeImmutable $now) : UserId|null
    {
        $key    = hash(algo: 'sha256', data: $token);
        $record = $this->records[$key] ?? null;
        unset($this->records[$key]);

        if ($record === null || $record['expires_at'] <= $now->getTimestamp()) {
            return null;
        }

        return new UserId(value: $record['user_id']);
    }

    public function pruneExpired(DateTimeImmutable $now) : int
    {
        $removed = 0;

        foreach ($this->records as $key => $record) {
            if ($record['expires_at'] > $now->getTimestamp()) {
                continue;
            }

            unset($this->records[$key]);
            $removed++;
        }

        return $removed;
    }
}
