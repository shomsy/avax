<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;
use Redis;
use SensitiveParameter;

class RedisSessionRegistry implements PruneExpiredSessionsInterface, SessionRegistryInterface
{
    private const string KEY_PREFIX = 'auth:session:';

    private const string USER_KEY_PREFIX = 'auth:user_sessions:';

    public function __construct(private readonly Redis $redis, private readonly int $ttlSeconds = 86400) {}

    public function save(SessionRecord $sessionRecord) : void
    {
        $this->track(record: $sessionRecord);
    }

    public function track(SessionRecord $sessionRecord) : void
    {
        $key = self::KEY_PREFIX . $sessionRecord->sessionId;

        $this->redis->hMset(
            key      : $key,
            fieldvals: [
                           'session_id'          => $sessionRecord->sessionId,
                           'user_id'             => $sessionRecord->userId->value,
                           'created_at'          => $sessionRecord->createdAt->format(format: DateTimeInterface::ATOM),
                           'last_seen_at'        => $sessionRecord->lastSeenAt->format(format: DateTimeInterface::ATOM),
                           'idle_expires_at'     => $sessionRecord->idleExpiresAt->format(format: DateTimeInterface::ATOM),
                           'absolute_expires_at' => $sessionRecord->absoluteExpiresAt->format(format: DateTimeInterface::ATOM),
                           'ip_created'          => $sessionRecord->ipCreated ?? '',
                           'user_agent_created'  => $sessionRecord->userAgentCreated ?? '',
                           'revoked_at'          => $sessionRecord->revokedAt?->format(format: DateTimeInterface::ATOM) ?? '',
                           'revoke_reason'       => $sessionRecord->revokeReason ?? '',
            ],
        );

        $this->redis->expire(key: $key, timeout: $this->ttlSeconds);

        $userKey = self::USER_KEY_PREFIX . $sessionRecord->userId->value;
        $this->redis->sAdd(key: $userKey, value: $sessionRecord->sessionId);
    }

    public function revokeForUser(UserId $userId, DateTimeImmutable $revokedAt, string $reason): void
    {
        $records = $this->listForUser(userId: $userId);

        foreach ($records as $record) {
            $this->revoke(sessionId: $record->sessionId, revokedAt: $revokedAt, reason: $reason);
        }
    }

    /**
     * @throws DateMalformedStringException
     */
    public function listForUser(UserId $userId): array
    {
        $userKey = self::USER_KEY_PREFIX . $userId->value;
        $sessionIds = $this->redis->sMembers(key: $userKey);

        $records = [];
        foreach ($sessionIds as $sessionId) {
            if ($sessionId === '') {
                continue;
            }

            $record = $this->find(sessionId: $sessionId);

            if ($record instanceof SessionRecord) {
                $records[] = $record;
            }
        }

        usort(
            array   : $records,
            callback: static fn (SessionRecord $left, SessionRecord $right): int => $right->lastSeenAt <=> $left->lastSeenAt,
        );

        return $records;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function find(#[SensitiveParameter] string $sessionId): ?SessionRecord
    {
        $key = self::KEY_PREFIX . $sessionId;
        $data = $this->redis->hGetAll(key: $key);

        if ($data === []) {
            return null;
        }

        return $this->hydrate(data: $data);
    }

    /**
     * @param array<string, string> $data
     *
     * @throws DateMalformedStringException
     */
    private function hydrate(array $data): SessionRecord
    {
        return new SessionRecord(
            sessionId        : $data['session_id'],
            userId           : new UserId(value: (int) $data['user_id']),
            createdAt        : new DateTimeImmutable(datetime: $data['created_at']),
            lastSeenAt       : new DateTimeImmutable(datetime: $data['last_seen_at']),
            idleExpiresAt    : new DateTimeImmutable(datetime: $data['idle_expires_at']),
            absoluteExpiresAt: new DateTimeImmutable(datetime: $data['absolute_expires_at']),
            ipCreated        : isset($data['ip_created']) && $data['ip_created'] !== '' ? $data['ip_created'] : null,
            userAgentCreated : isset($data['user_agent_created']) && $data['user_agent_created'] !== '' ? $data['user_agent_created'] : null,
            revokedAt        : isset($data['revoked_at']) && $data['revoked_at'] !== '' ? new DateTimeImmutable(datetime: $data['revoked_at']) : null,
            revokeReason     : isset($data['revoke_reason']) && $data['revoke_reason'] !== '' ? $data['revoke_reason'] : null,
        );
    }

    public function revoke(#[SensitiveParameter] string $sessionId, DateTimeImmutable $revokedAt, string $reason): void
    {
        $key = self::KEY_PREFIX . $sessionId;

        $this->redis->hSet($key, 'revoked_at', $revokedAt->format(format: DateTimeInterface::ATOM));
        $this->redis->hSet($key, 'revoke_reason', $reason);

        $data = $this->redis->hGetAll(key: $key);
        if (isset($data['user_id']) && $data['user_id'] !== '') {
            $userKey = self::USER_KEY_PREFIX . $data['user_id'];
            $this->redis->srem(key: $userKey, value: $sessionId);
        }
    }

    /**
     * @throws DateMalformedStringException
     */
    public function pruneExpired(DateTimeImmutable $now): int
    {
        $removed = 0;

        foreach ($this->redis->keys(pattern: self::KEY_PREFIX . '*') as $redi) {
            if ($redi === '') {
                continue;
            }

            $data = $this->redis->hGetAll(key: $redi);

            if ($data === []) {
                continue;
            }

            $record = $this->hydrate(data: $data);

            if (! $record->isRevoked() && ! $record->isExpiredAt(moment: $now)) {
                continue;
            }

            $this->redis->del(key: $redi);

            $userId = $data['user_id'] ?? null;
            $sessionId = $data['session_id'] ?? null;

            if (is_string(value: $userId) && $userId !== '' && is_string(value: $sessionId) && $sessionId !== '') {
                $this->redis->srem(key: self::USER_KEY_PREFIX . $userId, value: $sessionId);
            }

            $removed++;
        }

        return $removed;
    }
}
