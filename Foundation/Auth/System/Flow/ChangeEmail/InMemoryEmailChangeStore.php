<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\ChangeEmail;

use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;

final class InMemoryEmailChangeStore implements EmailChangeStoreInterface
{
    /** @var array<string, array{user_id: int, new_email: string, expires_at: int}> */
    private array $records = [];

    public function issue(UserId $userId, string $newEmail, DateTimeImmutable $expiresAt) : EmailChangeChallenge
    {
        $token = bin2hex(random_bytes(32));
        $this->records[hash('sha256', $token)] = [
            'user_id'    => $userId->value,
            'new_email'  => $newEmail,
            'expires_at' => $expiresAt->getTimestamp(),
        ];

        return new EmailChangeChallenge(
            dispatched: true,
            token     : $token,
            expiresAt : $expiresAt
        );
    }

    public function consume(string $token, DateTimeImmutable $now) : EmailChangeRecord|null
    {
        $key    = hash('sha256', $token);
        $record = $this->records[$key] ?? null;
        unset($this->records[$key]);

        if ($record === null || $record['expires_at'] <= $now->getTimestamp()) {
            return null;
        }

        return new EmailChangeRecord(
            userId   : new UserId($record['user_id']),
            newEmail : $record['new_email'],
            expiresAt: new DateTimeImmutable('@' . $record['expires_at'])
        );
    }
}
