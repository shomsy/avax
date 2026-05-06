<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RouteIntelligence;

/**
 * Represents a route conflict.
 */
final readonly class RouteConflict
{
    public const string CONFLICT_EXACT = 'exact';

    public const string CONFLICT_AMBIGUOUS = 'ambiguous';

    public const string CONFLICT_SHADOW = 'shadow';

    public function __construct(
        public string $type,
        public RouteInfo $routeA,
        public RouteInfo $routeB,
        public string $reason,
    ) {
    }

    public function isExact(): bool
    {
        return $this->type === self::CONFLICT_EXACT;
    }
}
