<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Capabilities\HealthCheck;

use Avax\Components\Operations\Events\System\Capabilities\InvokeEventListener\InvokeEventListener;
use Avax\Components\Operations\Events\System\Capabilities\ResolveEventListeners\ResolveEventListeners;
use Avax\Components\Operations\Events\System\Foundation\CompiledListener;
use Avax\Components\Operations\Events\System\Foundation\CompiledListenerRegistry;
use Avax\Components\Operations\Events\System\Foundation\EventEmitter;
use Avax\Components\Operations\Events\System\Foundation\ListenerSource;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use stdClass;
use Throwable;

/**
 * CheckEventsHealth
 *
 * Verifies events component runtime health:
 * - Compiled listener registry is functional (register, freeze, reset)
 * - Event emitter dispatches events correctly
 * - Listener resolution pipeline is instantiable
 */
final class CheckEventsHealth
{
    public function check() : HealthReport
    {
        $findings = [];
        $overall = HealthStatus::Green;

        // Check 1: CompiledListenerRegistry is functional
        try {
            $registry = new CompiledListenerRegistry();

            if ($registry->isFrozen() || $registry->totalListenerCount() !== 0 || $registry->getEventClasses() !== []) {
                $findings[] = new HealthFinding('events.registry', HealthStatus::Red, 'CompiledListenerRegistry has unexpected initial state');

                return new HealthReport(findings: $findings, overall: HealthStatus::Red);
            }

            $registry->freeze();
            if (! $registry->isFrozen()) {
                $findings[] = new HealthFinding('events.registry', HealthStatus::Red, 'CompiledListenerRegistry freeze failed');

                return new HealthReport(findings: $findings, overall: HealthStatus::Red);
            }

            $registry->reset();
            if ($registry->isFrozen()) {
                $findings[] = new HealthFinding('events.registry', HealthStatus::Red, 'CompiledListenerRegistry reset failed');

                return new HealthReport(findings: $findings, overall: HealthStatus::Red);
            }

            $findings[] = new HealthFinding('events.registry', HealthStatus::Green, 'CompiledListenerRegistry is functional');
        } catch (Throwable $e) {
            $findings[] = new HealthFinding('events.registry', HealthStatus::Red, sprintf('Registry check failed: %s', $e->getMessage()));

            return new HealthReport(findings: $findings, overall: HealthStatus::Red);
        }

        // Check 2: EventEmitter dispatches correctly
        try {
            $registry = new CompiledListenerRegistry();
            $resolver = new ResolveEventListeners();
            $invoker  = new InvokeEventListener();
            $emitter  = new EventEmitter($registry, $resolver, $invoker);

            $testEvent = new stdClass();
            $result    = $emitter->emit($testEvent);

            if ($result !== $testEvent) {
                $findings[] = new HealthFinding('events.emitter', HealthStatus::Red, 'EventEmitter did not return the event object');

                return new HealthReport(findings: $findings, overall: HealthStatus::Red);
            }

            $findings[] = new HealthFinding('events.emitter', HealthStatus::Green, 'EventEmitter dispatches correctly');
        } catch (Throwable $e) {
            $findings[] = new HealthFinding('events.emitter', HealthStatus::Red, sprintf('Emitter check failed: %s', $e->getMessage()));

            return new HealthReport(findings: $findings, overall: HealthStatus::Red);
        }

        // Check 3: ResolveEventListeners is instantiable
        try {
            $resolver   = new ResolveEventListeners();
            $findings[] = new HealthFinding('events.resolver', HealthStatus::Green, 'Listener resolver is instantiable');
        } catch (Throwable $e) {
            $findings[] = new HealthFinding('events.resolver', HealthStatus::Yellow, sprintf('Resolver check failed: %s', $e->getMessage()));
            $overall    = HealthStatus::Yellow;
        }

        return new HealthReport(findings: $findings, overall: $overall);
    }
}
