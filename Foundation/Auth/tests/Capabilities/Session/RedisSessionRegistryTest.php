<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Session;

use Avax\Auth\System\Capability\Session\RedisSessionRegistry;
use Avax\Auth\System\Capability\Session\SessionRecord;
use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Redis;
use SensitiveParameter;

final class RedisSessionRegistryTest extends TestCase
{
    public function testTrackPersistsFullSessionRecord() : void
    {
        $redis  = $this->createMock(Redis::class);
        $record = $this->record(sessionId: 'session-1', userId: 7);

        $redis->expects($this->once())
            ->method('hMset')
            ->with(
                'auth:session:session-1',
                $this->callback(static fn (array $payload) : bool => $payload['ip_created'] === '127.0.0.1'
                    && $payload['user_agent_created'] === 'Browser'
                    && $payload['revoked_at'] === '')
            )
            ->willReturn(true);
        $redis->expects($this->once())->method('expire')->with('auth:session:session-1', 86400)->willReturn(true);
        $redis->expects($this->once())->method('sAdd')->with('auth:user_sessions:7', 'session-1')->willReturn(1);

        (new RedisSessionRegistry(redis: $redis))->track(record: $record);
    }

    private function record(#[SensitiveParameter] string $sessionId, int $userId) : SessionRecord
    {
        return new SessionRecord(
            sessionId        : $sessionId,
            userId           : new UserId(value: $userId),
            createdAt        : new DateTimeImmutable(datetime: '2026-04-12T11:00:00+00:00'),
            lastSeenAt       : new DateTimeImmutable(datetime: '2026-04-12T12:05:00+00:00'),
            idleExpiresAt    : new DateTimeImmutable(datetime: '2026-04-12T12:30:00+00:00'),
            absoluteExpiresAt: new DateTimeImmutable(datetime: '2026-04-12T23:59:59+00:00'),
            ipCreated        : '127.0.0.1',
            userAgentCreated : 'Browser'
        );
    }

    public function testFindAndListForUserHydrateAndSortActiveSessions() : void
    {
        $redis    = $this->createMock(Redis::class);
        $registry = new RedisSessionRegistry(redis: $redis);

        $redis->expects($this->exactly(3))
            ->method('hGetAll')
            ->willReturnOnConsecutiveCalls(
                $this->row(sessionId: 'session-1', userId: 7, lastSeenAt: '2026-04-12T12:10:00+00:00'),
                $this->row(sessionId: 'session-2', userId: 7, lastSeenAt: '2026-04-12T12:00:00+00:00'),
                $this->row(sessionId: 'session-1', userId: 7, lastSeenAt: '2026-04-12T12:10:00+00:00')
            );
        $redis->expects($this->once())
            ->method('sMembers')
            ->with('auth:user_sessions:7')
            ->willReturn(['session-2', 'session-1']);

        $found  = $registry->find(sessionId: 'session-1');
        $listed = $registry->listForUser(userId: new UserId(value: 7));

        $this->assertSame(expected: 'session-1', actual: $found?->sessionId);
        $this->assertSame(expected: '127.0.0.1', actual: $found?->ipCreated);
        $this->assertCount(expectedCount: 2, haystack: $listed);
        $this->assertSame(expected: 'session-1', actual: $listed[0]->sessionId);
        $this->assertSame(expected: 'session-2', actual: $listed[1]->sessionId);
    }

    /**
     * @return array<string, string>
     */
    private function row(
        #[SensitiveParameter] string $sessionId,
        int                          $userId,
        string|null                  $lastSeenAt = null,
        string|null                  $idleExpiresAt = null,
        string|null                  $revokedAt = null,
        string                       $revokeReason = ''
    ) : array
    {
        $lastSeenAt    ??= '2026-04-12T12:05:00+00:00';
        $idleExpiresAt ??= '2026-04-12T12:30:00+00:00';
        $revokedAt     ??= '';

        return [
            'session_id'          => $sessionId,
            'user_id'             => (string) $userId,
            'created_at'          => '2026-04-12T11:00:00+00:00',
            'last_seen_at'        => $lastSeenAt,
            'idle_expires_at'     => $idleExpiresAt,
            'absolute_expires_at' => '2026-04-12T23:59:59+00:00',
            'ip_created'          => '127.0.0.1',
            'user_agent_created'  => 'Browser',
            'revoked_at'          => $revokedAt,
            'revoke_reason'       => $revokeReason,
        ];
    }

    public function testRevokeAndRevokeForUserUpdateRedisState() : void
    {
        $redis     = $this->createMock(Redis::class);
        $registry  = new RedisSessionRegistry(redis: $redis);
        $revokedAt = new DateTimeImmutable(datetime: '2026-04-12T12:15:00+00:00');
        $hsetCalls = [];
        $sremCalls = [];

        $redis->expects($this->exactly(4))
            ->method('hSet')
            ->willReturnCallback(static function (string $key, string $field, string $value) use (&$hsetCalls) : int {
                $hsetCalls[] = [$key, $field, $value];

                return 1;
            });
        $redis->expects($this->exactly(2))
            ->method('hGetAll')
            ->willReturnOnConsecutiveCalls(
                ['user_id' => '7', 'session_id' => 'session-1'],
                $this->row(sessionId: 'session-2', userId: 7)
            );
        $redis->expects($this->exactly(2))
            ->method('srem')
            ->willReturnCallback(static function (string $key, string $sessionId) use (&$sremCalls) : int {
                $sremCalls[] = [$key, $sessionId];

                return 1;
            });
        $redis->expects($this->once())
            ->method('sMembers')
            ->with('auth:user_sessions:7')
            ->willReturn(['session-2']);

        $registry->revoke('session-1', $revokedAt, 'logout');
        $registry->revokeForUser(new UserId(7), $revokedAt, 'logout_all');

        $this->assertSame(
            expected: [
                          ['auth:session:session-1', 'revoked_at', $revokedAt->format(format: DATE_ATOM)],
                          ['auth:session:session-1', 'revoke_reason', 'logout'],
                          ['auth:session:session-2', 'revoked_at', $revokedAt->format(format: DATE_ATOM)],
                          ['auth:session:session-2', 'revoke_reason', 'logout_all'],
                      ],
            actual  : $hsetCalls
        );
        $this->assertSame(
            expected: [
                          ['auth:user_sessions:7', 'session-1'],
                          ['auth:user_sessions:7', 'session-2'],
                      ],
            actual  : $sremCalls
        );
    }

    public function testPruneExpiredRemovesRevokedAndExpiredSessions() : void
    {
        $redis              = $this->createMock(Redis::class);
        $registry           = new RedisSessionRegistry(redis: $redis);
        $now                = new DateTimeImmutable(datetime: '2026-04-12T12:15:00+00:00');
        $deletedKeys        = [];
        $removedMemberships = [];

        $redis->expects(invocationRule: $this->once())
            ->method(constraint: 'keys')
            ->with('auth:session:*')
            ->willReturn(value: ['auth:session:expired', 'auth:session:revoked', 'auth:session:active']);
        $redis->expects(invocationRule: $this->exactly(3))
            ->method(constraint: 'hGetAll')
            ->willReturnOnConsecutiveCalls(
                $this->row(sessionId: 'expired', userId: 7, idleExpiresAt: '2026-04-12T12:00:00+00:00'),
                $this->row(sessionId: 'revoked', userId: 7, revokedAt: '2026-04-12T12:10:00+00:00', revokeReason: 'logout'),
                $this->row(sessionId: 'active', userId: 7, idleExpiresAt: '2026-04-12T13:00:00+00:00')
            );
        $redis->expects(invocationRule: $this->exactly(2))
            ->method(constraint: 'del')
            ->willReturnCallback(callback: static function (string $key) use (&$deletedKeys) : int {
                $deletedKeys[] = $key;

                return 1;
            });
        $redis->expects(invocationRule: $this->exactly(2))
            ->method(constraint: 'srem')
            ->willReturnCallback(callback: static function (string $key, #[SensitiveParameter] string $sessionId) use (&$removedMemberships) : int {
                $removedMemberships[] = [$key, $sessionId];

                return 1;
            });

        $removed = $registry->pruneExpired($now);

        $this->assertSame(expected: 2, actual: $removed);
        $this->assertSame(expected: ['auth:session:expired', 'auth:session:revoked'], actual: $deletedKeys);
        $this->assertSame(
            expected: [
                          ['auth:user_sessions:7', 'expired'],
                          ['auth:user_sessions:7', 'revoked'],
                      ],
            actual  : $removedMemberships
        );
    }

    protected function setUp(): void
    {
        if (! class_exists('Redis')) {
            self::markTestSkipped('Redis extension is not installed in this runtime.');
        }
    }
}
