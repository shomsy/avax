<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Session;

use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;
use Redis;
use RedisException;

class RedisSessionRegistry implements SessionRegistryInterface
{
    private const KEY_PREFIX = 'auth:session:';
    private const USER_KEY_PREFIX = 'auth:user_sessions:';

    public function __construct(
        private readonly Redis $redis,
        private readonly int $ttlSeconds = 86400,
    ) {}

    public function track(SessionRecord $record) : void
    {
        $key = self::KEY_PREFIX . $record->sessionId;
        
        $this->redis->hmset($key, [
            'session_id' => $record->sessionId,
            'user_id' => $record->userId->value,
            'created_at' => $record->createdAt->format(\DateTimeInterface::ATOM),
            'last_seen_at' => $record->lastSeenAt->format(\DateTimeInterface::ATOM),
            'idle_expires_at' => $record->idleExpiresAt->format(\DateTimeInterface::ATOM),
            'absolute_expires_at' => $record->absoluteExpiresAt->format(\DateTimeInterface::ATOM),
            'revoked_at' => $record->revokedAt?->format(\DateTimeInterface::ATOM) ?? '',
            'revoke_reason' => $record->revokeReason ?? '',
        ]);

        $this->redis->expire($key, $this->ttlSeconds);

        $userKey = self::USER_KEY_PREFIX . $record->userId->value;
        $this->redis->sadd($userKey, $record->sessionId);
    }

    public function find(string $sessionId) : SessionRecord|null
    {
        $key = self::KEY_PREFIX . $sessionId;
        $data = $this->redis->hgetall($key);

        if ($data === []) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function save(SessionRecord $record) : void
    {
        $this->track($record);
    }

    public function listForUser(UserId $userId) : array
    {
        $userKey = self::USER_KEY_PREFIX . $userId->value;
        $sessionIds = $this->redis->smembers($userKey);

        $records = [];
        foreach ($sessionIds as $sessionId) {
            $record = $this->find($sessionId);
            if ($record !== null && $record->revokedAt === null) {
                $records[] = $record;
            }
        }

        return $records;
    }

    public function revoke(string $sessionId, DateTimeImmutable $revokedAt, string $reason) : void
    {
        $key = self::KEY_PREFIX . $sessionId;
        
        $this->redis->hset($key, 'revoked_at', $revokedAt->format(\DateTimeInterface::ATOM));
        $this->redis->hset($key, 'revoke_reason', $reason);

        $data = $this->redis->hgetall($key);
        if (isset($data['user_id']) && $data['user_id'] !== '') {
            $userKey = self::USER_KEY_PREFIX . $data['user_id'];
            $this->redis->srem($userKey, $sessionId);
        }
    }

    public function revokeForUser(UserId $userId, DateTimeImmutable $revokedAt, string $reason) : void
    {
        $records = $this->listForUser($userId);
        
        foreach ($records as $record) {
            $this->revoke($record->sessionId, $revokedAt, $reason);
        }
    }

    /** @param array<string, string> $data */
    private function hydrate(array $data) : SessionRecord
    {
        return new SessionRecord(
            sessionId: $data['session_id'],
            userId: new UserId((int) $data['user_id']),
            createdAt: new DateTimeImmutable($data['created_at']),
            lastSeenAt: new DateTimeImmutable($data['last_seen_at']),
            idleExpiresAt: new DateTimeImmutable($data['idle_expires_at']),
            absoluteExpiresAt: new DateTimeImmutable($data['absolute_expires_at']),
            revokedAt: isset($data['revoked_at']) && $data['revoked_at'] !== '' ? new DateTimeImmutable($data['revoked_at']) : null,
            revokeReason: isset($data['revoke_reason']) && $data['revoke_reason'] !== '' ? $data['revoke_reason'] : null,
        );
    }
}
