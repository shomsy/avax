<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Policy\Capabilities\Rules;

use Closure;

final readonly class PolicyRule
{
    public function __construct(
        public string $action,
        public Closure $condition,
        public string $reason,
    ) {
    }

    public function applies(string $action): bool
    {
        return $this->action === $action;
    }

    public function evaluate(string $action, object $resource, array $context): ?PolicyDecision
    {
        $result = ($this->condition)($resource, $context);

        if ($result === true) {
            return new PolicyDecision(true, $this->reason);
        }

        if ($result === false) {
            return new PolicyDecision(false, $this->reason);
        }

        return null;
    }
}

final readonly class AttributeCondition
{
    public static function owner(object $resource, array $context): bool
    {
        $userId = $context['user_id'] ?? null;
        $ownerId = $resource->owner_id ?? null;

        return $userId === $ownerId;
    }

    public static function role(string $requiredRole, object $resource, array $context): bool
    {
        $userRole = $context['role'] ?? null;

        return $userRole === $requiredRole;
    }

    public static function withinHours(int $startHour, int $endHour): bool
    {
        $hour = (int) date('H');

        return $hour >= $startHour && $hour < $endHour;
    }

    public static function ipWhitelist(array $allowedIps, object $resource, array $context): bool
    {
        $ip = $context['ip'] ?? null;

        return in_array($ip, $allowedIps, true);
    }
}
