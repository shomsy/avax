<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Recover;

use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;

/**
 * In-memory password reset storage for tests and demos.
 */
final class InMemoryPasswordResetStore implements PasswordResetStoreInterface
{
    /** @var array<string, array{user_id: int, expires_at: int}> */
    private array $records = [];

    public function issue(UserId $userId, DateTimeImmutable $expiresAt) : PasswordResetChallenge
    {
        $token                                 = bin2hex(random_bytes(32));
        $this->records[hash('sha256', $token)] = [
            'user_id'    => $userId->value,
            'expires_at' => $expiresAt->getTimestamp(),
        ];

        return new PasswordResetChallenge(
            dispatched: true,
            token     : $token,
            expiresAt : $expiresAt
        );
    }

    public function consume(string $token, DateTimeImmutable $now) : UserId|null
    {
        $key    = hash('sha256', $token);
        $record = $this->records[$key] ?? null;
        unset($this->records[$key]);

        if ($record === null || $record['expires_at'] <= $now->getTimestamp()) {
            return null;
        }

        return new UserId($record['user_id']);
    }
}
