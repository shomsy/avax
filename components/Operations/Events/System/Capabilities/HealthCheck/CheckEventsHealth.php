<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Capabilities\HealthCheck;

use Avax\Components\Operations\Events\System\Foundation\CompiledListenerRegistry;
use Avax\Components\Operations\Events\System\Foundation\EventEmitter;

/**
 * CheckEventsHealth
 *
 * Verifies events component runtime health:
 * - Compiled listener registry available
 * - Event emitter/dispatcher available
 * - Listener resolution pipeline exists
 */
final readonly class CheckEventsHealth
{
    public function check(): EventsHealthReport
    {
        $findings = [];
        $healthy = true;

        // Check 1: Compiled listener registry available
        if (class_exists(CompiledListenerRegistry::class)) {
            $findings[] = 'Compiled listener registry available';
        } else {
            $healthy = false;
            $findings[] = 'Compiled listener registry class not loaded';
        }

        // Check 2: Event emitter available
        if (class_exists(EventEmitter::class)) {
            $findings[] = 'Event emitter available';
        } else {
            $healthy = false;
            $findings[] = 'Event emitter class not loaded';
        }

        // Check 3: ResolveEventListeners capability exists
        $resolverClass = 'Avax\\Components\\Operations\\Events\\System\\Capabilities\\ResolveEventListeners\\ResolveEventListeners';
        if (class_exists($resolverClass)) {
            $findings[] = 'Listener resolver available';
        } else {
            $healthy = false;
            $findings[] = 'Listener resolver class not loaded';
        }

        return new EventsHealthReport(
            healthy: $healthy,
            findings: $findings,
        );
    }
}
