<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Policy\Rules;

use DateTimeImmutable;

/**
 * Attribute-based conditions for policy evaluation.
 */
final readonly class AttributeCondition
{
    public static function owner(object $resource, array $context) : bool
    {
        $userId  = $context['user_id'] ?? null;
        $ownerId = $resource->owner_id ?? null;

        return $userId === $ownerId;
    }

    public static function role(string $requiredRole, object $resource, array $context) : bool
    {
        $userRole = $context['role'] ?? null;

        return $userRole === $requiredRole;
    }

    public static function withinHours(
        int $startHour,
        int $endHour,
        DateTimeImmutable|null $currentTime = null,
    ) : bool
    {
        $hour = (int) ($currentTime ?? new DateTimeImmutable())->format('H');

        return $hour >= $startHour && $hour < $endHour;
    }

    public static function ipWhitelist(array $allowedIps, object $resource, array $context) : bool
    {
        $ip = $context['ip'] ?? null;

        return in_array($ip, $allowedIps, true);
    }
}
