<?php

declare(strict_types=1);

namespace Avax\Auth\Examples\Persistence;

use Avax\Auth\System\Capabilities\Session\SessionRecord;
use Avax\Auth\System\Capabilities\Session\SessionRegistryInterface;
use Avax\Auth\System\Capabilities\User\UserId;
use DateMalformedStringException;
use DateTimeImmutable;
use PDO;
use SensitiveParameter;

/**
 * Reference PDO implementation for the durable session registry contract.
 */
final readonly class PdoSessionRegistry implements SessionRegistryInterface
{
    private PDO $pdo;

    public function __construct(
        PDO $pdo
    )
    {
        $this->pdo = $pdo;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function find(#[SensitiveParameter] string $sessionId) : SessionRecord|null
    {
        $statement = $this->pdo->prepare(query: 'SELECT * FROM auth_sessions WHERE session_id = :session_id LIMIT 1');
        $statement->execute(params: ['session_id' => $sessionId]);
        $row = $statement->fetch(mode: PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate(row: $row) : null;
    }

    /**
     * @param array<string, mixed> $row
     *
     * @throws DateMalformedStringException
     * @throws DateMalformedStringException
     * @throws DateMalformedStringException
     * @throws DateMalformedStringException
     * @throws DateMalformedStringException
     */
    private function hydrate(array $row) : SessionRecord
    {
        return new SessionRecord(
            sessionId        : (string) $row['session_id'],
            userId           : new UserId(value: (int) $row['user_id']),
            createdAt        : new DateTimeImmutable(datetime: (string) $row['created_at']),
            lastSeenAt       : new DateTimeImmutable(datetime: (string) $row['last_seen_at']),
            idleExpiresAt    : new DateTimeImmutable(datetime: (string) $row['idle_expires_at']),
            absoluteExpiresAt: new DateTimeImmutable(datetime: (string) $row['absolute_expires_at']),
            ipCreated        : isset($row['ip_created']) ? (string) $row['ip_created'] : null,
            userAgentCreated : isset($row['user_agent_created']) ? (string) $row['user_agent_created'] : null,
            revokedAt        : isset($row['revoked_at']) && $row['revoked_at'] !== null
                                   ? new DateTimeImmutable(datetime: (string) $row['revoked_at'])
                                   : null,
            revokeReason     : isset($row['revoke_reason']) ? (string) $row['revoke_reason'] : null
        );
    }

    public function save(SessionRecord $record) : void
    {
        $this->track(record: $record);
    }

    public function track(SessionRecord $record) : void
    {
        $statement = $this->pdo->prepare(
            query: 'INSERT INTO auth_sessions (
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

        $statement->execute(params: $this->mapRecord(record: $record));
    }

    /**
     * @return array<string, string|int|null>
     */
    private function mapRecord(SessionRecord $record) : array
    {
        return [
            'session_id'          => $record->sessionId,
            'user_id'             => $record->userId->value,
            'created_at'          => $record->createdAt->format(format: DATE_ATOM),
            'last_seen_at'        => $record->lastSeenAt->format(format: DATE_ATOM),
            'idle_expires_at'     => $record->idleExpiresAt->format(format: DATE_ATOM),
            'absolute_expires_at' => $record->absoluteExpiresAt->format(format: DATE_ATOM),
            'ip_created'          => $record->ipCreated,
            'user_agent_created'  => $record->userAgentCreated,
            'revoked_at'          => $record->revokedAt?->format(format: DATE_ATOM),
            'revoke_reason'       => $record->revokeReason,
        ];
    }

    public function listForUser(UserId $userId) : array
    {
        $statement = $this->pdo->prepare(
            query: 'SELECT * FROM auth_sessions WHERE user_id = :user_id ORDER BY last_seen_at DESC'
        );
        $statement->execute(params: ['user_id' => $userId->value]);
        $rows = $statement->fetchAll(mode: PDO::FETCH_ASSOC);

        return array_map(/**
         * @throws DateMalformedStringException
         */ fn (array $row) : SessionRecord => $this->hydrate(row: $row), $rows);
    }

    public function revoke(#[SensitiveParameter] string $sessionId, DateTimeImmutable $revokedAt, string $reason) : void
    {
        $statement = $this->pdo->prepare(
            query: 'UPDATE auth_sessions SET revoked_at = :revoked_at, revoke_reason = :revoke_reason WHERE session_id = :session_id'
        );
        $statement->execute(params: [
                                        'session_id'    => $sessionId,
                                        'revoked_at'    => $revokedAt->format(format: DATE_ATOM),
                                        'revoke_reason' => $reason,
                                    ]);
    }

    public function revokeForUser(UserId $userId, DateTimeImmutable $revokedAt, string $reason) : void
    {
        $statement = $this->pdo->prepare(
            query: 'UPDATE auth_sessions SET revoked_at = :revoked_at, revoke_reason = :revoke_reason WHERE user_id = :user_id'
        );
        $statement->execute(params: [
                                        'user_id'       => $userId->value,
                                        'revoked_at'    => $revokedAt->format(format: DATE_ATOM),
                                        'revoke_reason' => $reason,
                                    ]);
    }
}
