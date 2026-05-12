<?php

declare(strict_types=1);

namespace Avax\Examples\SecureRegistrationApi;

/**
 * RecordRegistrationAudit — Invokable listener that records audit data
 * when a user registers.
 *
 * No ListenerInterface required. Plain invokable class.
 * Registered through onEvent(UserRegistered::class)->do(...).
 */
final class RecordRegistrationAudit
{
    /** @var list<array{userId: string, email: string, action: string, timestamp: string}> */
    public static array $auditLog = [];

    public function __invoke(UserRegistered $event): void
    {
        self::$auditLog[] = [
            'userId' => $event->userId,
            'email' => $event->email,
            'action' => 'user.registered',
            'timestamp' => $event->registeredAt,
        ];
    }

    public static function reset(): void
    {
        self::$auditLog = [];
    }
}
