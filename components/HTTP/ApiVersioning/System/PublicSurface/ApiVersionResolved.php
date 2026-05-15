<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ApiVersioning\System\PublicSurface;

use DateTimeInterface;

/**
 * ApiVersionResolved — immutable result value for API version resolution.
 *
 * Carries the resolved version number, deprecation flag, and optional sunset date.
 * Produced by ApiVersion::resolve() and consumed by downstream middleware/handlers.
 * Not a service, not a capability — a pure data transfer object.
 */
final readonly class ApiVersionResolved
{
    public function __construct(
        public int                $version,
        public bool               $deprecated,
        public DateTimeInterface|null $sunset = null,
    ) {}
}
