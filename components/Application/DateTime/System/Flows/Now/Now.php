<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Flows\Now;

final class Now
{
    public static function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    public static function today(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('today');
    }

    public static function yesterday(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('yesterday');
    }

    public static function tomorrow(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('tomorrow');
    }

    public static function utc(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public static function fromTimestamp(int $timestamp): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat('U', (string) $timestamp);
    }
}