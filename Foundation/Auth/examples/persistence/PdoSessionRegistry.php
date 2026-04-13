<?php

declare(strict_types=1);

namespace Avax\Auth\Examples\Persistence;

use Avax\Auth\System\Capability\Session\SessionRecord;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;
use PDO;

/**
 * Reference PDO implementation for the durable session registry contract.
 */
final readonly class PdoSessionRegistry implements SessionRegistryInterface
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function track(SessionRecord $record) : void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO auth_sessions (
                session_id, user_id, created_at, last_seen_at, idle_expires_at, absolute_expires_at,
                ip_created, user_agent_created, revoked_at, revoke_reason
            ) VALUES (
                :session_id, :user_id, :created_at, :last_seen_at, :idle_expires_at, :absolute_expires_at,
                :ip_created, :user_agent_created, :revoked_at, :revoke_reason
            )
            ON CONFLICT (session_id) DO UPDATE SET
                last_seen_at = EXCLUDED.last_seen_at,
                idle_expires_at = EXCLUDED.idle_expires_at,
                absolute_expires_at = EXCLUDED.absolute_expires_at,
                ip_created = COALESCE(EXCLUDED.ip_created, auth_sessions.ip_created),
                user_agent_created = COALESCE(EXCLUDED.user_agent_created, auth_sessions.user_agent_created),
                revoked_at = EXCLUDED.revoked_at,
                revoke_reason = EXCLUDED.revoke_reason'
        );

        $statement->execute($this->mapRecord($record));
    }

    public function find(string $sessionId) : SessionRecord|null
    {
        $statement = $this->pdo->prepare('SELECT * FROM auth_sessions WHERE session_id = :session_id LIMIT 1');
        $statement->execute(['session_id' => $sessionId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function save(SessionRecord $record) : void
    {
        $this->track($record);
    }

    public function listForUser(UserId $userId) : array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM auth_sessions WHERE user_id = :user_id ORDER BY last_seen_at DESC'
        );
        $statement->execute(['user_id' => $userId->value]);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn (array $row) : SessionRecord => $this->hydrate($row), $rows);
    }

    public function revoke(string $sessionId, DateTimeImmutable $revokedAt, string $reason) : void
    {
        $statement = $this->pdo->prepare(
            'UPDATE auth_sessions SET revoked_at = :revoked_at, revoke_reason = :revoke_reason WHERE session_id = :session_id'
        );
        $statement->execute([
            'session_id' => $sessionId,
            'revoked_at' => $revokedAt->format(DATE_ATOM),
            'revoke_reason' => $reason,
        ]);
    }

    public function revokeForUser(UserId $userId, DateTimeImmutable $revokedAt, string $reason) : void
    {
        $statement = $this->pdo->prepare(
            'UPDATE auth_sessions SET revoked_at = :revoked_at, revoke_reason = :revoke_reason WHERE user_id = :user_id'
        );
        $statement->execute([
            'user_id' => $userId->value,
            'revoked_at' => $revokedAt->format(DATE_ATOM),
            'revoke_reason' => $reason,
        ]);
    }

    /**
     * @return array<string, string|int|null>
     */
    private function mapRecord(SessionRecord $record) : array
    {
        return [
            'session_id' => $record->sessionId,
            'user_id' => $record->userId->value,
            'created_at' => $record->createdAt->format(DATE_ATOM),
            'last_seen_at' => $record->lastSeenAt->format(DATE_ATOM),
            'idle_expires_at' => $record->idleExpiresAt->format(DATE_ATOM),
            'absolute_expires_at' => $record->absoluteExpiresAt->format(DATE_ATOM),
            'ip_created' => $record->ipCreated,
            'user_agent_created' => $record->userAgentCreated,
            'revoked_at' => $record->revokedAt?->format(DATE_ATOM),
            'revoke_reason' => $record->revokeReason,
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row) : SessionRecord
    {
        return new SessionRecord(
            sessionId        : (string) $row['session_id'],
            userId           : new UserId((int) $row['user_id']),
            createdAt        : new DateTimeImmutable((string) $row['created_at']),
            lastSeenAt       : new DateTimeImmutable((string) $row['last_seen_at']),
            idleExpiresAt    : new DateTimeImmutable((string) $row['idle_expires_at']),
            absoluteExpiresAt: new DateTimeImmutable((string) $row['absolute_expires_at']),
            ipCreated        : isset($row['ip_created']) ? (string) $row['ip_created'] : null,
            userAgentCreated : isset($row['user_agent_created']) ? (string) $row['user_agent_created'] : null,
            revokedAt        : isset($row['revoked_at']) && $row['revoked_at'] !== null
                ? new DateTimeImmutable((string) $row['revoked_at'])
                : null,
            revokeReason     : isset($row['revoke_reason']) ? (string) $row['revoke_reason'] : null
        );
    }
}
