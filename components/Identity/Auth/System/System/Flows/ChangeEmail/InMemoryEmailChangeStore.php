<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Flows\ChangeEmail;

use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\User\UserId;
use DateMalformedStringException;
use DateTimeImmutable;
use Random\RandomException;
use SensitiveParameter;

final class InMemoryEmailChangeStore implements EmailChangeStoreInterface
{
    /** @var array<string, array{user_id: int, new_email: string, expires_at: int}> */
    private array $records = [];

    /**
     * @throws RandomException
     */
    public function issue(UserId $userId, #[SensitiveParameter] string $newEmail, DateTimeImmutable $expiresAt) : EmailChangeChallenge
    {
        $token                                             = bin2hex(string: random_bytes(length: 32));
        $this->records[hash(algo: 'sha256', data: $token)] = [
            'user_id'    => $userId->value,
            'new_email'  => $newEmail,
            'expires_at' => $expiresAt->getTimestamp(),
        ];

        return new EmailChangeChallenge(
            dispatched: true,
            token     : $token,
            expiresAt : $expiresAt,
        );
    }

    /**
     * @throws DateMalformedStringException
     */
    public function consume(#[SensitiveParameter] string $token, DateTimeImmutable $now) : ?EmailChangeRecord
    {
        $key    = hash(algo: 'sha256', data: $token);
        $record = $this->records[$key] ?? null;
        unset($this->records[$key]);

        if ($record === null || $record['expires_at'] <= $now->getTimestamp()) {
            return null;
        }

        return new EmailChangeRecord(
            userId   : new UserId(value: $record['user_id']),
            newEmail : $record['new_email'],
            expiresAt: new DateTimeImmutable(datetime: '@' . $record['expires_at']),
        );
    }
}
