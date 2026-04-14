<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Session;

use Avax\Auth\System\Capability\Session\PdoSessionRegistry;
use Avax\Auth\System\Capability\Session\SessionRecord;
use Avax\Auth\System\Capability\User\UserId;
use DateInterval;
use DateTimeImmutable;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

final class PdoSessionRegistryTest extends TestCase
{
    public function testTrackPersistsSessionRecord() : void
    {
        $pdo = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);
        $record = $this->buildRecord();

        $pdo->expects($this->once())
            ->method('prepare')
            ->with($this->callback(static fn (string $query) : bool => str_contains($query, 'INSERT INTO auth_sessions')))
            ->willReturn($statement);

        $statement->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function (array $params) use ($record) : bool {
                return $params['session_id'] === $record->sessionId
                    && $params['user_id'] === $record->userId->value
                    && $params['revoked_at'] === null
                    && $params['revoke_reason'] === null;
            }))
            ->willReturn(true);

        $registry = new PdoSessionRegistry(pdo: $pdo);
        $registry->track(record: $record);
    }

    public function testFindAndListForUserHydrateRows() : void
    {
        $pdo = $this->createMock(PDO::class);
        $findStatement = $this->createMock(PDOStatement::class);
        $listStatement = $this->createMock(PDOStatement::class);
        $registry = new PdoSessionRegistry(pdo: $pdo);

        $pdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($findStatement, $listStatement);

        $findStatement->expects($this->once())
            ->method('execute')
            ->with(['session_id' => 'session-1'])
            ->willReturn(true);
        $findStatement->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($this->row(sessionId: 'session-1', userId: 77));

        $listStatement->expects($this->once())
            ->method('execute')
            ->with(['user_id' => 77])
            ->willReturn(true);
        $listStatement->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([
                $this->row(sessionId: 'session-2', userId: 77, lastSeenAt: '2026-04-12T11:30:00+00:00'),
                $this->row(sessionId: 'session-1', userId: 77, lastSeenAt: '2026-04-12T12:00:00+00:00'),
            ]);

        $record = $registry->find(sessionId: 'session-1');
        $sessions = $registry->listForUser(userId: new UserId(value: 77));

        $this->assertSame(expected: 'session-1', actual: $record?->sessionId);
        $this->assertSame(expected: 77, actual: $record?->userId->value);
        $this->assertCount(expectedCount: 2, haystack: $sessions);
        $this->assertSame(expected: 'session-2', actual: $sessions[0]->sessionId);
        $this->assertSame(expected: 'session-1', actual: $sessions[1]->sessionId);
    }

    public function testRevokeAndPruneExpiredSessionStatementsAreIssued() : void
    {
        $pdo = $this->createMock(PDO::class);
        $revokeStatement = $this->createMock(PDOStatement::class);
        $revokeForUserStatement = $this->createMock(PDOStatement::class);
        $pruneStatement = $this->createMock(PDOStatement::class);
        $registry = new PdoSessionRegistry(pdo: $pdo);

        $pdo->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($revokeStatement, $revokeForUserStatement, $pruneStatement);

        $revokeStatement->expects($this->once())
            ->method('execute')
            ->with($this->callback(static fn (array $params) : bool => $params['session_id'] === 'session-1' && $params['revoke_reason'] === 'logout'))
            ->willReturn(true);

        $revokeForUserStatement->expects($this->once())
            ->method('execute')
            ->with($this->callback(static fn (array $params) : bool => $params['user_id'] === 77 && $params['revoke_reason'] === 'logout_all'))
            ->willReturn(true);

        $pruneStatement->expects($this->once())
            ->method('execute')
            ->with($this->callback(static fn (array $params) : bool => isset($params['now']) && is_string($params['now'])))
            ->willReturn(true);
        $pruneStatement->expects($this->once())
            ->method('rowCount')
            ->willReturn(2);

        $registry->revoke(sessionId: 'session-1', revokedAt: new DateTimeImmutable(datetime: '2026-04-12T12:05:00+00:00'), reason: 'logout');
        $registry->revokeForUser(userId: new UserId(value: 77), revokedAt: new DateTimeImmutable(datetime: '2026-04-12T12:06:00+00:00'), reason: 'logout_all');
        $removed = $registry->pruneExpired(now: new DateTimeImmutable(datetime: '2026-04-12T12:07:00+00:00'));

        $this->assertSame(expected: 2, actual: $removed);
    }

    /**
     * @return array<string, scalar|null>
     */
    private function row(string $sessionId, int $userId, string $lastSeenAt = '2026-04-12T12:00:00+00:00') : array
    {
        return [
            'session_id' => $sessionId,
            'user_id' => $userId,
            'created_at' => '2026-04-12T11:00:00+00:00',
            'last_seen_at' => $lastSeenAt,
            'idle_expires_at' => '2026-04-12T12:15:00+00:00',
            'absolute_expires_at' => '2026-04-12T23:59:59+00:00',
            'ip_created' => '127.0.0.1',
            'user_agent_created' => 'Browser',
            'revoked_at' => null,
            'revoke_reason' => null,
        ];
    }

    private function buildRecord() : SessionRecord
    {
        return new SessionRecord(
            sessionId        : 'session-1',
            userId           : new UserId(value: 77),
            createdAt        : new DateTimeImmutable(datetime: '2026-04-12T11:00:00+00:00'),
            lastSeenAt       : new DateTimeImmutable(datetime: '2026-04-12T12:00:00+00:00'),
            idleExpiresAt    : new DateTimeImmutable(datetime: '2026-04-12T12:15:00+00:00'),
            absoluteExpiresAt: new DateTimeImmutable(datetime: '2026-04-12T23:59:59+00:00'),
            ipCreated        : '127.0.0.1',
            userAgentCreated : 'Browser'
        );
    }
}
