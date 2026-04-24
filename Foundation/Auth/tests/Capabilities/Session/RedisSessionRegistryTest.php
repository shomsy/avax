<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\Session;

use Avax\Auth\System\Capabilities\Identity\Sessions\Registry\RedisSessionRegistry;
use Avax\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRecord;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Tests\TestCase;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Redis;
use SensitiveParameter;

final class RedisSessionRegistryTest extends TestCase
{
    public function testTrackPersistsFullSessionRecord() : void
    {
        $redis  = $this->createMock(Redis::class);
        $record = $this->record();

        $redis->expects($this->once())
            ->method(constraint: 'hMset')
            ->with(
                'auth:session:session-1',
                $this->callback(callback: static fn (array $payload) : bool => $payload['ip_created'] === '127.0.0.1'
                    && $payload['user_agent_created'] === 'Browser'
                    && $payload['revoked_at'] === '')
            )
            ->willReturn(value: true);
        $redis->expects($this->once())->method(constraint: 'expire')->with('auth:session:session-1', 86400)->willReturn(value: true);
        $redis->expects($this->once())->method(constraint: 'sAdd')->with('auth:user_sessions:7', 'session-1')->willReturn(value: 1);

        (new RedisSessionRegistry(redis: $redis))->track(record: $record);
    }

    private function record() : SessionRecord
    {
        return new SessionRecord(
            sessionId        : 'session-1',
            userId           : new UserId(value: 7),
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
            ->method(constraint: 'hGetAll')
            ->willReturnOnConsecutiveCalls(
                $this->row(sessionId: 'session-1', lastSeenAt: '2026-04-12T12:10:00+00:00'),
                $this->row(sessionId: 'session-2', lastSeenAt: '2026-04-12T12:00:00+00:00'),
                $this->row(sessionId: 'session-1', lastSeenAt: '2026-04-12T12:10:00+00:00')
            );
        $redis->expects($this->once())
            ->method(constraint: 'sMembers')
            ->with('auth:user_sessions:7')
            ->willReturn(value: ['session-2', 'session-1']);

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
        string|null                  $lastSeenAt = null,
        string|null                  $idleExpiresAt = null,
        string|null                  $revokedAt = null,
        string                       $revokeReason = ''
    ) : array
    {
        $userId        = 7;
        $lastSeenAt    ??= '2026-04-12T12:05:00+00:00';
        $idleExpiresAt ??= '2026-04-12T12:30:00+00:00';
        $revokedAt     ??= '';

        return [
            'session_id'          => $sessionId,
            'user_id'             => (string) (7),
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
            ->method(constraint: 'hSet')
            ->willReturnCallback(callback: static function (string $key, string $field, string $value) use (&$hsetCalls) : int {
                $hsetCalls[] = [$key, $field, $value];

                return 1;
            });
        $redis->expects($this->exactly(2))
            ->method(constraint: 'hGetAll')
            ->willReturnOnConsecutiveCalls(
                ['user_id' => '7', 'session_id' => 'session-1'],
                $this->row(sessionId: 'session-2')
            );
        $redis->expects($this->exactly(2))
            ->method(constraint: 'srem')
            ->willReturnCallback(callback: static function (string $key, #[SensitiveParameter] string $sessionId) use (&$sremCalls) : int {
                $sremCalls[] = [$key, $sessionId];

                return 1;
            });
        $redis->expects($this->once())
            ->method(constraint: 'sMembers')
            ->with('auth:user_sessions:7')
            ->willReturn(value: ['session-2']);

        $registry->revoke(sessionId: 'session-1', revokedAt: $revokedAt, reason: 'logout');
        $registry->revokeForUser(userId: new UserId(value: 7), revokedAt: $revokedAt, reason: 'logout_all');

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

        $redis->expects($this->once())
            ->method(constraint: 'keys')
            ->with('auth:session:*')
            ->willReturn(value: ['auth:session:expired', 'auth:session:revoked', 'auth:session:active']);
        $redis->expects($this->exactly(3))
            ->method(constraint: 'hGetAll')
            ->willReturnOnConsecutiveCalls(
                $this->row(sessionId: 'expired', lastSeenAt: '2026-04-12T12:00:00+00:00', idleExpiresAt: '2026-04-12T12:00:00+00:00'),
                $this->row(sessionId: 'revoked', lastSeenAt: '2026-04-12T12:10:00+00:00', idleExpiresAt: 'logout', revokedAt: '2026-04-12T12:10:00+00:00', revokeReason: 'logout'),
                $this->row(sessionId: 'active', lastSeenAt: '2026-04-12T13:00:00+00:00', idleExpiresAt: '2026-04-12T13:00:00+00:00')
            );
        $redis->expects($this->exactly(2))
            ->method(constraint: 'del')
            ->willReturnCallback(callback: static function (string $key) use (&$deletedKeys) : int {
                $deletedKeys[] = $key;

                return 1;
            });
        $redis->expects($this->exactly(2))
            ->method(constraint: 'srem')
            ->willReturnCallback(callback: static function (string $key, #[SensitiveParameter] string $sessionId) use (&$removedMemberships) : int {
                $removedMemberships[] = [$key, $sessionId];

                return 1;
            });

        $removed = $registry->pruneExpired(now: $now);

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
        if (! class_exists(class: 'Redis')) {
            self::markTestSkipped(message: 'Redis extension is not installed in this runtime.');
        }
    }
}
