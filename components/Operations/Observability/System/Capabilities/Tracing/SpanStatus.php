<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Tracing;

/**
 * Canonical span status values as a backed enum.
 *
 * Replaces bare string literals like 'ok', 'error' with type-safe enum cases.
 */
enum SpanStatus: string
{
    case Ok = 'ok';
    case Error = 'error';
    case Unset = 'unset';

    /**
     * Create a SpanStatus from a string value.
     */
    public static function fromString(string $status): self
    {
        return match (strtolower($status)) {
            'ok' => self::Ok,
            'error' => self::Error,
            'unset' => self::Unset,
            default => self::Unset,
        };
    }
}
