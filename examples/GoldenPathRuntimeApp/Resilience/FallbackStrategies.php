<?php

declare(strict_types=1);

namespace Avax\Examples\GoldenPathRuntimeApp\Resilience;

/**
 * Callable factories for Fallback::execute chains.
 *
 * Each static method returns a callable that can be passed to
 * Fallback::execute as part of a fallback strategy chain.
 */
final class FallbackStrategies
{
    /**
     * Returns a degraded response when the external webhook service is unavailable.
     */
    public static function externalServiceDegraded() : callable
    {
        return static fn () : array => [
            'status'  => 'degraded',
            'message' => 'External service unavailable, webhook queued for later processing',
        ];
    }

    /**
     * Returns a cached response when the primary lookup fails.
     */
    public static function cachedWebhookStatus(string $webhookId) : callable
    {
        return static fn () => [
            'webhook_id' => $webhookId,
            'status'     => 'cached',
            'state'      => 'accepted',
        ];
    }
}
