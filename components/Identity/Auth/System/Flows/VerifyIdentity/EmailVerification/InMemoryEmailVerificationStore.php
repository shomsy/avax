<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;
use Random\RandomException;
use SensitiveParameter;

/**
 * In-memory verification challenge storage for tests and demos.
 */
final class InMemoryEmailVerificationStore implements EmailVerificationStoreInterface
{
    /** @var array<string, array{user_id: int, expires_at: int}> */
    private array $records = [];

    /**
     * @throws RandomException
     */
    public function issue(UserId $userId, DateTimeImmutable $expiresAt) : EmailVerificationChallenge
    {
        $token                                             = bin2hex(string: random_bytes(length: 32));
        $this->records[hash(algo: 'sha256', data: $token)] = [
            'user_id'    => $userId->value,
            'expires_at' => $expiresAt->getTimestamp(),
        ];

        return new EmailVerificationChallenge(
            dispatched: true,
            token     : $token,
            expiresAt : $expiresAt,
        );
    }

    public function consume(#[SensitiveParameter] string $token, DateTimeImmutable $now) : ?UserId
    {
        $key    = hash(algo: 'sha256', data: $token);
        $record = $this->records[$key] ?? null;
        unset($this->records[$key]);

        if ($record === null || $record['expires_at'] <= $now->getTimestamp()) {
            return null;
        }

        return new UserId(value: $record['user_id']);
    }
}
